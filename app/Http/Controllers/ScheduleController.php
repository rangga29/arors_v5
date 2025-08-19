<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use App\Models\Log;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Services\APIHeaderGenerator;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use function dump;

class ScheduleController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator)
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
    }

    public function index($date)
    {
        $this->authorize('view', Schedule::class);

        return view('backend.schedules.view', [
            'date_original' => $date,
            'date' => Carbon::parse($date)->isoFormat('dddd, DD MMMM YYYY'),
            'schedule_date_first' => ScheduleDate::orderBy('sd_date', 'ASC')->first()->sd_date,
            'schedule_date_last' => ScheduleDate::orderBy('sd_date', 'DESC')->first()->sd_date,
            'schedules' => Schedule::where('sd_id', ScheduleDate::where('sd_date', $date)->first()->id)
                ->orderBy('sc_clinic_name')
                ->orderBy('sc_doctor_name')
                ->get()
        ]);
    }

    public function available($date, Request $request, Schedule $schedule)
    {
        $this->authorize('update', Schedule::class);

        $date = ScheduleDate::where('id', $schedule->sd_id)->first()->sd_date;
        $detail = ScheduleDetail::where('sc_id', $schedule->id)->first();
        if($schedule->sc_available) {
            $schedule->update([
                'sc_available' => false,
                'updated_by' => auth()->user()->name
            ]);

            $detail->update(['scd_available' => false]);

            Log::create([
                'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user' => auth()->user()->username,
                'lo_ip' => \Request::ip(),
                'lo_module' => 'SCHEDULE',
                'lo_message' => 'Non Activated Schedule | ' . $date . ' -- ' . $schedule->sc_clinic_code . ' -- ' . $schedule->sc_clinic_name
            ]);

            return redirect()->route('schedules', $date)->with('success', 'Jadwal ' . $schedule->sc_clinic_name . ' Untuk ' . $schedule->sc_clinic_name . ' Berhasil Di Non Aktifkan.');
        } else {
            $schedule->update([
                'sc_available' => true,
                'updated_by' => auth()->user()->name
            ]);

            $detail->update(['scd_available' => true]);

            Log::create([
                'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
                'lo_user' => auth()->user()->username,
                'lo_ip' => \Request::ip(),
                'lo_module' => 'SCHEDULE',
                'lo_message' => 'Activated Schedule | ' . $date . ' -- ' . $schedule->sc_clinic_code . ' -- ' . $schedule->sc_clinic_name
            ]);

            return redirect()->route('schedules', $date)->with('success', 'Jadwal ' . $schedule->sc_clinic_name . ' Untuk ' . $schedule->sc_doctor_name . ' Berhasil Di Aktifkan.');
        }
    }

    public function update($date, Schedule $schedule, $session)
    {
        $responses = [];
        $link = env('API_KEY', 'rsck');
        $clinic = Clinic::where('cl_code', $schedule['sc_clinic_code'])->pluck('cl_code')->first();
        $schedule_date = Carbon::create($date)->format('Ymd');
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $type = 'success';
        $message = 'Update Jadwal ' .  $schedule['sc_doctor_name'] . ' Tanggal ' . Carbon::create($date)->isoFormat('DD MMMM YYYY') . ' Berhasil Dilakukan.';

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 3 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $client = new Client(['handler' => $handlerStack, 'verify' => false]);
            $response = $client->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/physician/available/{$schedule_date}/{$clinic}", [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if (!empty($data['Data'])) {
                    $dataField = json_decode($data['Data'], true);
                    for ($x = 0; $x < count($dataField); $x++) {
                        if($dataField[$x]['PhysicianCode'] === $schedule['sc_doctor_code']) {
                            if($dataField[$x]['PhysicianOperationalTime']['OperationalTimeCode'] === $schedule['sc_operational_time_code']) {
                                $scheduleDetailUpdate = ScheduleDetail::where('sc_id', $schedule['id'])->update([
                                    'scd_start_time' => $dataField[$x]['PhysicianOperationalTime']["StartTime{$session}"],
                                    'scd_end_time' => $dataField[$x]['PhysicianOperationalTime']["EndTime{$session}"],
                                    'scd_umum' => $dataField[$x]['PhysicianOperationalTime']["IsNonBPJS{$session}"],
                                    'scd_bpjs' => $dataField[$x]['PhysicianOperationalTime']["IsBPJS{$session}"],
                                    'scd_max_umum' => $dataField[$x]['PhysicianOperationalTime']["MaximumAppointmentNonBPJS{$session}"],
                                    'scd_max_bpjs' => $dataField[$x]['PhysicianOperationalTime']["MaximumAppointmentBPJS{$session}"],
                                    'scd_online_umum' => $dataField[$x]['PhysicianOperationalTime']["OnlineAppointmentNonBPJS{$session}"],
                                    'scd_online_bpjs' => $dataField[$x]['PhysicianOperationalTime']["OnlineAppointmentBPJS{$session}"],
                                ]);
                            } else {
                                $scheduleDateData = ScheduleDate::where('sd_date', $date)->first();
                                for ($y = 1; $y <= 5; $y++) {
                                    $startTimeKey = "StartTime{$y}";
                                    $endTimeKey = "EndTime{$y}";

                                    $scheduleDoctors = Schedule::join('schedule_details', 'schedules.id', '=', 'schedule_details.sc_id')
                                        ->where('schedules.sd_id', $scheduleDateData->id)
                                        ->where('schedules.sc_doctor_code', $dataField[$x]['PhysicianCode'])
                                        ->where('schedules.sc_clinic_code', $dataField[$x]['PhysicianOperationalTime']['ServiceUnitCode'])
                                        ->where('schedule_details.scd_session', $y)
                                        ->first();

                                    if ($dataField[$x]['PhysicianOperationalTime'][$startTimeKey] == '') {
                                        break;
                                    }

                                    if($scheduleDoctors) {
                                        $scheduleUpdate = Schedule::where('id', $scheduleDoctors->sc_id)->update([
                                            'sc_operational_time_code' => $dataField[$x]['PhysicianOperationalTime']['OperationalTimeCode'],
                                            'updated_by' => auth()->user()->username,
                                        ]);
                                        $scheduleDetailUpdate = ScheduleDetail::where('sc_id', $scheduleDoctors->sc_id)->update([
                                            'scd_start_time' => $dataField[$x]['PhysicianOperationalTime'][$startTimeKey],
                                            'scd_end_time' => $dataField[$x]['PhysicianOperationalTime'][$endTimeKey],
                                            'scd_umum' => $dataField[$x]['PhysicianOperationalTime']["IsNonBPJS{$y}"],
                                            'scd_bpjs' => $dataField[$x]['PhysicianOperationalTime']["IsBPJS{$y}"],
                                            'scd_max_umum' => $dataField[$x]['PhysicianOperationalTime']["MaximumAppointmentNonBPJS{$y}"],
                                            'scd_max_bpjs' => $dataField[$x]['PhysicianOperationalTime']["MaximumAppointmentBPJS{$y}"],
                                            'scd_online_umum' => $dataField[$x]['PhysicianOperationalTime']["OnlineAppointmentNonBPJS{$y}"],
                                            'scd_online_bpjs' => $dataField[$x]['PhysicianOperationalTime']["OnlineAppointmentBPJS{$y}"],
                                        ]);
                                    } else {
                                        do {
                                            $randomString = Str::random(20);
                                        } while (Schedule::where('sc_ucode', $randomString)->exists());

                                        $sc_id_temp = Schedule::create([
                                            'sd_id' => $scheduleDateData->id,
                                            'sc_ucode' => $randomString,
                                            'sc_doctor_code' => $dataField[$x]['PhysicianCode'],
                                            'sc_doctor_name' => $dataField[$x]['PhysicianName'],
                                            'sc_clinic_code' => $dataField[$x]['PhysicianOperationalTime']['ServiceUnitCode'],
                                            'sc_clinic_name' => $dataField[$x]['PhysicianOperationalTime']['ServiceUnitName'],
                                            'sc_operational_time_code' => $dataField[$x]['PhysicianOperationalTime']['OperationalTimeCode'],
                                            'sc_available' => true,
                                            'created_by' => auth()->user()->username,
                                        ]);

                                        ScheduleDetail::create([
                                            'sc_id' => $sc_id_temp->id,
                                            'scd_session' => $y,
                                            'scd_start_time' => $dataField[$x]['PhysicianOperationalTime'][$startTimeKey],
                                            'scd_end_time' => $dataField[$x]['PhysicianOperationalTime'][$endTimeKey],
                                            'scd_umum' => $dataField[$x]['PhysicianOperationalTime']["IsNonBPJS{$y}"],
                                            'scd_bpjs' => $dataField[$x]['PhysicianOperationalTime']["IsBPJS{$y}"],
                                            'scd_counter_max_umum' => 0,
                                            'scd_max_umum' => $dataField[$x]['PhysicianOperationalTime']["MaximumAppointmentNonBPJS{$y}"],
                                            'scd_counter_max_bpjs' => 0,
                                            'scd_max_bpjs' => $dataField[$x]['PhysicianOperationalTime']["MaximumAppointmentBPJS{$y}"],
                                            'scd_counter_online_umum' => 0,
                                            'scd_online_umum' => $dataField[$x]['PhysicianOperationalTime']["OnlineAppointmentNonBPJS{$y}"],
                                            'scd_counter_online_bpjs' => 0,
                                            'scd_online_bpjs' => $dataField[$x]['PhysicianOperationalTime']["OnlineAppointmentBPJS{$y}"],
                                            'scd_available' => true,
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                $type = 'danger';
                $message = response()->json(['error' => 'Request failed'], $response->getStatusCode());
            }
        } catch (RequestException $e) {
            $type = 'danger';
            $message = response()->json(['error' => $e->getMessage()], 500);
        }

        date_default_timezone_set('Asia/Jakarta');
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'SCHEDULE',
            'lo_message' => 'UPDATE ' .  $schedule['sc_doctor_name'] . ' TANGGAL ' . Carbon::create($date)->isoFormat('DD MMMM YYYY')
        ]);
        return redirect()->route('schedules', $date)->with($type, $message);
    }

    public function printSchedule($date)
    {
        $fileName = Carbon::createFromFormat('Y-m-d', $date)->format('Ymd') . '_JadwalDokter';
        $data = [
            'title' => $fileName,
            'date' => $date,
            'scheduleData' => Schedule::where('sd_id', ScheduleDate::where('sd_date', $date)->first()->id)
                ->where('sc_available', true)
                ->get()
        ];

        $pdf = PDF::loadView('backend.schedules.print', $data)->setPaper('a4', 'landscape');
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }

    public function show(Schedule $schedule)
    {
        $this->authorize('update', Schedule::class);

        $data = ScheduleDetail::where('sc_id', $schedule->id)->first();

        return response()->json($data);
    }

    public function updateQuota($date, Schedule $schedule, Request $request)
    {
        $this->authorize('update', Schedule::class);

        $scheduleDetail = ScheduleDetail::where('sc_id', $schedule->id)->first();

        if($scheduleDetail->scd_counter_online_umum > $request['scd_online_umum'] || $scheduleDetail->scd_counter_online_bpjs > $request['scd_online_bpjs']) {
            return back()->with('danger', 'Kuota Maksimal Umum / BPJS Lebih Kecil Dari Yang Sudah Digunakan');
        }

        $scheduleDetail->update([
            'scd_online_umum' => $request['scd_online_umum'],
            'scd_online_bpjs' => $request['scd_online_bpjs'],
            'scd_umum' => $request->has('scd_umum') ? $request['scd_umum'] : 0,
            'scd_bpjs' => $request->has('scd_bpjs') ? $request['scd_bpjs'] : 0,
            'updated_by' => auth()->user()->username
        ]);

        date_default_timezone_set('Asia/Jakarta');
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'SCHEDULE',
            'lo_message' => 'UPDATE KUOTA ' .  $schedule['sc_doctor_name'] . ' TANGGAL ' . Carbon::create($date)->isoFormat('DD MMMM YYYY')
        ]);
        return redirect()->route('schedules', $date)->with('success', 'Kuota Dokter ' . $schedule['sc_doctor_name'] . ' Tanggal ' . Carbon::create($date)->isoFormat('DD MMMM YYYY') . ' Berhasil Diubah');
    }
}
