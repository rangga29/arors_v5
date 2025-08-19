<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AppointmentBackup;
use App\Models\AsuransiAppointment;
use App\Models\BpjsKesehatanAppointment;
use App\Models\Clinic;
use App\Models\FisioterapiAppointment;
use App\Models\FisioterapiAppointmentBackup;
use App\Models\Log;
use App\Models\NewAppointment;
use App\Models\Schedule;
use App\Models\ScheduleBackup;
use App\Models\ScheduleDate;
use App\Models\ScheduleDateBackup;
use App\Models\ScheduleDetail;
use App\Models\ScheduleDetailBackup;
use App\Models\UmumAppointment;
use App\Services\APIHeaderGenerator;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Exception\RequestException;

class ScheduleDateController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator)
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
    }

    public function index()
    {
        $this->authorize('viewDate', ScheduleDate::class);

        return view('backend.schedules.view-date', [
            'schedule_dates' => ScheduleDate::where('sd_date', '>=', Carbon::today()->toDateString())->orderBy('sd_date', 'ASC')->get(),
            'schedule_date_first' => ScheduleDate::orderBy('sd_date', 'ASC')->first()->sd_date,
            'schedule_date_last' => ScheduleDate::orderBy('sd_date', 'DESC')->first()->sd_date,
        ]);
    }

    public function showRedirect(Request $request)
    {
        $this->authorize('view', Schedule::class);

        return redirect()->route('schedules', $request['schedule-date']);
    }

    public function store(Request $request)
    {
        $this->authorize('createDate', ScheduleDate::class);

        $currentDate = Carbon::create(ScheduleDate::orderBy('sd_date', 'DESC')->first()->sd_date)->addDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $request->download_date);

        while ($currentDate <= $endDate) {
            do {
                $ucode = Str::random(20);
                $ucodeCheck = ScheduleDate::where('sd_ucode', $ucode)->exists();
            } while ($ucodeCheck);
            if ($currentDate->dayOfWeek === CarbonInterface::SUNDAY) {
                ScheduleDate::create([
                    'sd_ucode' => $ucode,
                    'sd_date' => $currentDate,
                    'sd_is_downloaded' => false,
                    'sd_is_holiday' => false,
                    'sd_holiday_desc' => 'Sunday Clinic',
                    'created_by' => $request->created_by,
                    'updated_by' => null,
                ]);
            } else {
                ScheduleDate::create([
                    'sd_ucode' => $ucode,
                    'sd_date' => $currentDate,
                    'sd_is_downloaded' => false,
                    'sd_is_holiday' => false,
                    'sd_holiday_desc' => null,
                    'created_by' => $request->created_by,
                    'updated_by' => null,
                ]);
            }
            $currentDate->addDay();
        }

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'SCHEDULE DATE',
            'lo_message' => 'CREATE : Until ' . $endDate . ' And Backup Older Date'
        ]);
        return redirect()->route('schedules.dates')->with('success', 'Data Tanggal Berhasil Ditambah');
    }

    public function download(ScheduleDate $scheduleDate)
    {
        $this->authorize('download', Schedule::class);

        $responses = [];
        $link = env('API_KEY', 'rsck');
        $clinics = Clinic::where('cl_active', true)->orderBy('cl_name')->pluck('cl_code')->all();
        $schedule_date = Carbon::create($scheduleDate['sd_date'])->format('Ymd');
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $type = 'success';
        $message = 'Download Jadwal Tanggal ' . Carbon::create($scheduleDate['sd_date'])->isoFormat('DD MMMM YYYY') . ' Berhasil Dilakukan.';

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 3 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        foreach ($clinics as $clinic) {
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
                            for ($y = 1; $y <= 5; $y++) {
                                $startTimeKey = "StartTime{$y}";
                                $endTimeKey = "EndTime{$y}";

                                if ($dataField[$x]['PhysicianOperationalTime'][$startTimeKey] == '') {
                                    break;
                                }

                                do {
                                    $randomString = Str::random(20);
                                } while (Schedule::where('sc_ucode', $randomString)->exists());

                                $sc_id_temp = Schedule::create([
                                    'sd_id' => $scheduleDate->id,
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
                    $scheduleDate->update([
                        'sd_is_downloaded' => true
                    ]);
                } else {
                    $type = 'danger';
                    $message = response()->json(['error' => 'Request failed'], $response->getStatusCode());
                }
            } catch (RequestException $e) {
                $type = 'danger';
                $message = response()->json(['error' => $e->getMessage()], 500);
            }
        }

        date_default_timezone_set('Asia/Jakarta');
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'SCHEDULE',
            'lo_message' => 'DOWNLOAD : ' . $scheduleDate['sd_date']
        ]);
        return redirect()->route('schedules.dates')->with($type, $message);
    }

    public function downloadUpdate(ScheduleDate $scheduleDate)
    {
        $this->authorize('update', Schedule::class);

        $responses = [];
        $link = env('API_KEY', 'rsck');
        $clinics = Clinic::where('cl_active', true)->orderBy('cl_name')->pluck('cl_code')->all();
        $schedule_date = Carbon::create($scheduleDate['sd_date'])->format('Ymd');
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $type = 'success';
        $message = 'Update Download Jadwal Tanggal ' . Carbon::create($scheduleDate['sd_date'])->isoFormat('DD MMMM YYYY') . ' Berhasil Dilakukan.';

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 3 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        foreach ($clinics as $clinic) {
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
                            for ($y = 1; $y <= 5; $y++) {
                                $startTimeKey = "StartTime{$y}";
                                $endTimeKey = "EndTime{$y}";

                                if ($dataField[$x]['PhysicianOperationalTime'][$startTimeKey] == '') {
                                    break;
                                }

                                $scheduleDoctors = Schedule::join('schedule_details', 'schedules.id', '=', 'schedule_details.sc_id')
                                    ->where('schedules.sd_id', $scheduleDate->id)
                                    ->where('schedules.sc_doctor_code', $dataField[$x]['PhysicianCode'])
                                    ->where('schedules.sc_clinic_code', $dataField[$x]['PhysicianOperationalTime']['ServiceUnitCode'])
                                    ->where('schedule_details.scd_session', $y)
                                    ->first();

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
                                        'sd_id' => $scheduleDate->id,
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
                } else {
                    $type = 'danger';
                    $message = response()->json(['error' => 'Request failed'], $response->getStatusCode());
                }
            } catch (RequestException $e) {
                $type = 'danger';
                $message = response()->json(['error' => $e->getMessage()], 500);
            }
        }

        date_default_timezone_set('Asia/Jakarta');
        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'SCHEDULE',
            'lo_message' => 'UPDATE DOWNLOAD : ' . $scheduleDate['sd_date']
        ]);
        return redirect()->route('schedules.dates')->with($type, $message);
    }

    public function show(ScheduleDate $scheduleDate)
    {
        $this->authorize('editDate', ScheduleDate::class);

        $data = ScheduleDate::where('sd_ucode', $scheduleDate->sd_ucode)->first();
        return response()->json($data);
    }

    public function update(Request $request, ScheduleDate $scheduleDate)
    {
        $this->authorize('editDate', ScheduleDate::class);

        $validateData = $request->validate([
            'sd_is_holiday' => 'required|boolean',
            'sd_holiday_desc' => 'nullable|required_if:sd_is_holiday,false'
        ],[
            'sd_holiday_desc.required_if' => 'Deskripsi Libur Harus Diisi'
        ]);

        if(!$validateData['sd_is_holiday']) {
            $validateData['sd_holiday_desc'] = '';
        }
        $scheduleDate->update($validateData);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'SCHEDULE DATE',
            'lo_message' => 'UPDATE : ' . $scheduleDate->sd_date
        ]);
        return redirect()->route('schedules.dates')->with('success', 'Data Tanggal Berhasil Diubah');
    }

    public function destroy(ScheduleDate $scheduleDate)
    {
        $this->authorize('delete', Schedule::class);

        $schedules = Schedule::where('sd_id', $scheduleDate->id)->get();
        foreach ($schedules as $schedule) {
            $scheduleDetails = ScheduleDetail::where('sc_id', $schedule->id)->get();
            foreach ($scheduleDetails as $scheduleDetail) {
                $scheduleDetail->delete();
            }
            $schedule->delete();
        }

        $scheduleDate->update([
            'sd_is_downloaded' => false
        ]);

        Log::create([
            'lo_time' => Carbon::now()->format('Y-m-d H:i:s'),
            'lo_user' => auth()->user()->username,
            'lo_ip' => \Request::ip(),
            'lo_module' => 'SCHEDULE',
            'lo_message' => 'DELETE : ' . $scheduleDate->sd_date
        ]);
        return redirect()->route('schedules.dates')->with('success', 'Hapus Jadwal Tanggal ' . Carbon::parse($scheduleDate['sd_date'])->isoFormat('DD MMMM YYYY') . ' Berhasil Dilakukan.');
    }
}
