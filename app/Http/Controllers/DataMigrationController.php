<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\AsuransiAppointment;
use App\Models\BpjsKesehatanAppointment;
use App\Models\FisioterapiAppointment;
use App\Models\NewAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
use App\Services\APIHeaderGenerator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use League\Csv\Reader;
use function redirect;
use function strtoupper;

class DataMigrationController extends Controller
{

    protected APIHeaderGenerator $apiHeaderGenerator;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator)
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
    }

    public function index()
    {
        return view('backend.data-migration.view', [
            'schedule_dates' => ScheduleDate::where('sd_date', '>=', Carbon::today()->toDateString())
                ->where('sd_is_downloaded', true)
                ->orderBy('sd_date', 'ASC')->get(),
        ]);
    }

    public function dataMigration(Request $request)
    {
        $file = $request->file('csv_file');
        try {
            $csv = Reader::createFromPath($file->getRealPath(), 'r');
            $csv->setHeaderOffset(0);
            $records = $csv->getRecords();
            $recordsArray = iterator_to_array($records);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }

        $schedule_date = ScheduleDate::where('sd_ucode', $request->selectedScheduleDate)->first();

        if($request->selectedType == 'fisio') {
            foreach ($recordsArray as $record) {
                if($record['fap_type'] == 'fisio_umum_pagi') {
                    $typeChange = 'UMUM PAGI';
                } elseif ($record['fap_type'] == 'fisio_umum_sore') {
                    $typeChange = 'UMUM SORE';
                } elseif ($record['fap_type'] == 'fisio_bpjs_pagi') {
                    $typeChange = 'BPJS PAGI';
                } else {
                    $typeChange = 'BPJS SORE';
                }

                FisioterapiAppointment::create([
                    'sd_id' => $schedule_date['id'],
                    'fap_ucode' => $record['fap_ucode'],
                    'fap_token' => $record['fap_token'],
                    'fap_type' => $typeChange,
                    'fap_queue' => $record['fap_queue'],
                    'fap_registration_time' => $record['fap_registration_time'],
                    'fap_appointment_time' => $record['fap_appointment_time'],
                    'fap_norm' => $record['fap_norm'],
                    'fap_name' => $record['fap_name'],
                    'fap_birthday' => Carbon::createFromFormat('d/m/Y', $record['fap_birthday'])->format('Y-m-d'),
                    'fap_gender' => $record['fap_gender'],
                    'fap_phone' => $record['fap_phone'],
                ]);
            }
        } else {
            foreach ($recordsArray as $record) {
                $schedule = Schedule::where('sc_doctor_code', $record['sc_doctor_code'])
                    ->where('sc_clinic_code', $record['sc_clinic_code'])
                    ->where('sc_operational_time_code', $record['sc_operational_time_code'])
                    ->first();
                if($schedule) {
                    $scheduleDetail = ScheduleDetail::where('sc_id', $schedule->id)->first();

                    if($request->selectedType == 'umum') {
                        $appointmentData = Appointment::create([
                            'scd_id' => $scheduleDetail['id'],
                            'ap_ucode' => $record['ap_ucode'],
                            'ap_no' => $record['ap_no'],
                            'ap_token' => $record['ap_token'],
                            'ap_queue' => $record['ap_queue'],
                            'ap_type' => $record['ap_type'],
                            'ap_registration_time' => $record['ap_registration_time'],
                            'ap_appointment_time' => $record['ap_appointment_time']
                        ]);
                        UmumAppointment::create([
                            'ap_id' => $appointmentData['id'],
                            'uap_norm' => $record['uap_norm'],
                            'uap_name' => $record['uap_name'],
                            'uap_birthday' => Carbon::createFromFormat('d/m/Y', $record['uap_birthday'])->format('Y-m-d'),
                            'uap_gender' => $record['uap_gender'],
                            'uap_phone' => $record['uap_phone'],
                        ]);
                        if($scheduleDetail['scd_counter_online_umum'] >= $scheduleDetail['scd_online_umum'] && $scheduleDetail['scd_counter_online_bpjs'] <= $scheduleDetail['scd_online_bpjs'] ) {
                            ScheduleDetail::where('id', $scheduleDetail['id'])->decrement('scd_max_bpjs');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->decrement('scd_online_bpjs');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_max_umum');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_online_umum');
                        }
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_max_umum');
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_online_umum');
                    } elseif ($request->selectedType == 'asuransi') {
                        $appointmentData = Appointment::create([
                            'scd_id' => $scheduleDetail['id'],
                            'ap_ucode' => $record['ap_ucode'],
                            'ap_no' => $record['ap_no'],
                            'ap_token' => $record['ap_token'],
                            'ap_queue' => $record['ap_queue'],
                            'ap_type' => $record['ap_type'],
                            'ap_registration_time' => $record['ap_registration_time'],
                            'ap_appointment_time' => $record['ap_appointment_time']
                        ]);
                        AsuransiAppointment::create([
                            'ap_id' => $appointmentData['id'],
                            'aap_norm' => $record['aap_norm'],
                            'aap_name' => $record['aap_name'],
                            'aap_birthday' => Carbon::createFromFormat('d/m/Y', $record['aap_birthday'])->format('Y-m-d'),
                            'aap_gender' => $record['aap_gender'],
                            'aap_phone' => $record['aap_phone'],
                            'aap_business_partner_code' => 'AAA',
                            'aap_business_partner_name' => $record['aap_business_partner']
                        ]);
                        if($scheduleDetail['scd_counter_online_umum'] >= $scheduleDetail['scd_online_umum'] && $scheduleDetail['scd_counter_online_bpjs'] <= $scheduleDetail['scd_online_bpjs'] ) {
                            ScheduleDetail::where('id', $scheduleDetail['id'])->decrement('scd_max_bpjs');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->decrement('scd_online_bpjs');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_max_umum');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_online_umum');
                        }
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_max_umum');
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_online_umum');
                    } elseif ($request->selectedType == 'bpjs') {
                        $appointmentData = Appointment::create([
                            'scd_id' => $scheduleDetail['id'],
                            'ap_ucode' => $record['ap_ucode'],
                            'ap_no' => $record['ap_no'],
                            'ap_token' => $record['ap_token'],
                            'ap_queue' => $record['ap_queue'],
                            'ap_type' => $record['ap_type'],
                            'ap_registration_time' => $record['ap_registration_time'],
                            'ap_appointment_time' => $record['ap_appointment_time']
                        ]);
                        BpjsKesehatanAppointment::create([
                            'ap_id' => $appointmentData['id'],
                            'bap_norm' => $record['bap_norm'],
                            'bap_name' => $record['bap_name'],
                            'bap_birthday' => Carbon::createFromFormat('d/m/Y', $record['bap_birthday'])->format('Y-m-d'),
                            'bap_gender' => $record['bap_gender'],
                            'bap_phone' => $record['bap_phone'],
                            'bap_bpjs' => $record['bap_bpjs'],
                            'bap_ppk1' => $record['bap_ppk1']
                        ]);
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_max_bpjs');
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_online_bpjs');
                    } elseif ($request->selectedType == 'baru') {
                        $appointmentData = Appointment::create([
                            'scd_id' => $scheduleDetail['id'],
                            'ap_ucode' => $record['ap_ucode'],
                            'ap_no' => $record['ap_no'],
                            'ap_token' => $record['ap_token'],
                            'ap_queue' => $record['ap_queue'],
                            'ap_type' => $record['ap_type'],
                            'ap_registration_time' => $record['ap_registration_time'],
                            'ap_appointment_time' => $record['ap_appointment_time']
                        ]);
                        NewAppointment::create([
                            'ap_id' => $appointmentData['id'],
                            'nap_norm' => '00-00-00-00',
                            'nap_name' => $record['nap_name'],
                            'nap_birthday' => Carbon::createFromFormat('d/m/Y', $record['nap_birthday'])->format('Y-m-d'),
                            'nap_phone' => $record['nap_phone'],
                            'nap_ssn' => $record['nap_ssn'],
                            'nap_gender' => $record['nap_gender'],
                            'nap_address' => $record['nap_address'],
                            'nap_email' => $record['nap_email']
                        ]);
                        if($scheduleDetail['scd_counter_online_umum'] >= $scheduleDetail['scd_online_umum'] && $scheduleDetail['scd_counter_online_bpjs'] <= $scheduleDetail['scd_online_bpjs'] ) {
                            ScheduleDetail::where('id', $scheduleDetail['id'])->decrement('scd_max_bpjs');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->decrement('scd_online_bpjs');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_max_umum');
                            ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_online_umum');
                        }
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_max_umum');
                        ScheduleDetail::where('id', $scheduleDetail['id'])->increment('scd_counter_online_umum');
                    }
                }
            }
        }
        return redirect()->route('data-migration')->with('success', 'Migrasi Berhasil Dilakukan');
    }

    public function printOldSep()
    {
        return view('backend.data-migration.view-print-sep');
    }

    public function getPrintOldSep(Request $request)
    {
        $request->validate([
            'kode_list' => 'required|string'
        ]);

        $status = [];
        $link = env('API_KEY', 'rsck');

        $kodeArray = array_filter(array_map('trim', explode("\n", $request->kode_list)));

        $headers = $this->apiHeaderGenerator->generateApiHeader();
        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        foreach ($kodeArray as $kode) {
            $requestData = [
                'Parameter' => $kode,
            ];

            try {
                $client = new Client(['handler' => $handlerStack, 'verify' => false]);
                $response = $client->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/v2/centerback/bpjs/sep/print", [
                    'headers' => $headers,
                    'form_params' => $requestData
                ]);
                $statusCode = $response->getStatusCode();
                $status[] = [
                    'kode' => $kode,
                    'status' => $statusCode == 200 ? 'Berhasil' : 'Gagal',
                    'http_code' => $statusCode
                ];
            } catch (RequestException $e) {
                $status[] = [
                    'kode' => $kode,
                    'status' => 'Exception',
                    'http_code' => $e->getCode()
                ];
            }
        }

        $totalInput = count($kodeArray);
        $totalStatus = count($status);
        $isMatch = $totalInput === $totalStatus;

        $summary = [
            'total_input' => $totalInput,
            'total_processed' => $totalStatus,
            'match' => $isMatch
        ];

        return redirect()->route('data-migration.print-old-sep')
            ->with('success', 'Proses selesai')
            ->with('status_detail', $status)
            ->with('status_summary', $summary);
    }
}
