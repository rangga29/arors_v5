<?php

namespace App\Http\Controllers\Version2;

use App\Http\Controllers\Controller;
use App\Models\BpjsKesehatanAppointment;
use App\Models\PatientTemporary;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Services\APIBpjsHeaderGenerator;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use App\Services\AppointmentOpen;
use App\Services\NormConverter;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LZCompressor\LZString;

class OldBPJSController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected APIBpjsHeaderGenerator $apiBpjsHeaderGenerator;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, APIBpjsHeaderGenerator $apiBpjsHeaderGenerator, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate)
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->apiBpjsHeaderGenerator = $apiBpjsHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
    }

    public function index()
    {
        return view('version2.bpjs-1', [
            'background' => 'bpjs',
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ]);
    }

    public function patientCheck(Request $request)
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return back();
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($request->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();
        $headerBpjs = $this->apiBpjsHeaderGenerator->generateApiBpjsHeader();

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $request->birthday)->format('Ymd');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah');
        }

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $client = new Client(['handler' => $handlerStack, 'verify' => false]);
            $response = $client->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/patient/{$medicalNo}", [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if (isset($data['Data'])) {
                    $dataField = json_decode($data['Data'], true);
                    if ($birthdate == $dataField['DateOfBirth']) {
                        $handlerStackBpjs = HandlerStack::create();
                        $handlerStackBpjs->push(Middleware::retry(function ($retry, $request, $response, $exception) {
                            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
                        }, function ($retry) {
                            return 1000 * pow(2, $retry);
                        }));

                        do {
                            try {
                                $clientBpjs = new Client(['handler' => $handlerStackBpjs, 'verify' => false]);
                                $responseBpjs = $clientBpjs->get("https://apijkn.bpjs-kesehatan.go.id/vclaim-rest/rujukan/{$request->ppk1}", [
                                    'headers' => $headerBpjs,
                                ]);

                                if ($responseBpjs->getStatusCode() == 200)
                                {
                                    $dataBpjs = json_decode($responseBpjs->getBody(), true);
                                    if($dataBpjs['metaData']['code'] == 200)
                                    {
                                        date_default_timezone_set('UTC');
                                        $bpjs_time_stamp = $headerBpjs['X-timestamp'];

                                        $bpjs_consumer_id = "25796";
                                        $bpjs_consumer_secret = "4qP1E30D6D";

                                        $bpjs_key_dec = $bpjs_consumer_id . $bpjs_consumer_secret . $bpjs_time_stamp;
                                        $bpjs_key_hash = hex2bin(hash('SHA256', $bpjs_key_dec));
                                        $bpjs_key_iv = substr($bpjs_key_hash, 0, 16);

                                        for ($i = 1; $i <= 10000; $i++) {
                                            $bpjs_decryptResult = openssl_decrypt(base64_decode($dataBpjs['response']), 'AES-256-CBC', $bpjs_key_hash, OPENSSL_RAW_DATA, $bpjs_key_iv);
                                            if(!$bpjs_decryptResult) {
                                                if ($i === 10000) {
                                                    return back()->with('error', 'Terjadi Kesalahan. Silahkan Dicoba Kembali.');
                                                }
                                            } else {
                                                break;
                                            }
                                        }

                                        $bpjs_unCompressedResult = LZString::decompressFromEncodedURIComponent($bpjs_decryptResult);
                                        $bpjs_result = json_decode($bpjs_unCompressedResult, TRUE);

                                        if($bpjs_result['rujukan']['peserta']['mr']['noMR'] === $dataField['MedicalNo'] || $bpjs_result['rujukan']['peserta']['nik'] === $dataField['SSN'])
                                        {
                                            do {
                                                $token = Str::random(20);
                                                $tokenCheck = PatientTemporary::where('pt_ucode', $token)->exists();
                                            } while ($tokenCheck);
                                            $patient_temp = PatientTemporary::create([
                                                'pt_ucode' => $token,
                                                'pt_norm' => $request->norm,
                                                'pt_name' => $dataField['FullName'],
                                                'pt_birthday' => Carbon::createFromFormat('Ymd', $dataField['DateOfBirth'])->format('Y-m-d'),
                                                'pt_gender' => $dataField['Gender'],
                                                'pt_ssn' => $bpjs_result['rujukan']['peserta']['nik'],
                                                'pt_poli' => $bpjs_result['rujukan']['poliRujukan']['kode'],
                                                'pt_bpjs' => $bpjs_result['rujukan']['peserta']['noKartu'],
                                                'pt_ppk1' => $request->ppk1
                                            ]);
                                            return redirect()->route('old-bpjs.appointment-create', $patient_temp['pt_ucode']);
                                        } else {
                                            return back()->with('error', 'No Rujukan Tidak Cocok Dengan No Rekam Medis / NIK Pasien.');
                                        }
                                    } else {
                                        return back()->with('error', 'No Rujukan Tidak Ditemukan atau Salah.');
                                    }
                                } else {
                                    return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
                                }
                            } catch (RequestException $e) {
                                return back()->with('error', 'An error occurred: ' . $e->getMessage());
                            }
                        } while (!$bpjs_decryptResult);
                    } else {
                        return back()->with('error', 'Data Pasien Tidak Cocok');
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan');
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function appointmentCreate(PatientTemporary $patientTemporary)
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return redirect()->route('old-bpjs');
        }

        $appointmentDate = $this->appointmentDate->selectAppointmentDate();

        $clinicsWithDoctors = Schedule::query()
            ->where('sc_available', true)
            ->whereHas('scheduleDate', function ($query) use ($appointmentDate) {
                $query->where('sd_date', $appointmentDate);
            })
            ->join('clinics', 'schedules.sc_clinic_code', '=', 'clinics.cl_code')
            ->join('schedule_details', 'schedules.id', '=', 'schedule_details.sc_id')
            ->where('clinics.cl_code_bpjs', $patientTemporary['pt_poli'])
            ->where('clinics.cl_active', true)
            ->where('clinics.cl_bpjs', true)
            ->where('schedule_details.scd_available', true)
            ->where('schedule_details.scd_bpjs', true)
            ->orderBy('clinics.cl_order')
            ->get()
            ->groupBy('sc_clinic_name');

        return view('version2.bpjs-2', [
            'background' => 'bpjs',
            'patient' => $patientTemporary,
            'dates' => ScheduleDate::where('sd_date', $this->appointmentDate->selectAppointmentDate())->get(),
            'clinicsWithDoctors' => $clinicsWithDoctors,
        ]);
    }

    public function appointmentStore(Request $request, PatientTemporary $patientTemporary)
    {
        $link = env('API_KEY', 'rsck');

        $scheduleDetailData = ScheduleDetail::where('id', $request->selectedSchedule)->first();
        $scheduleData = Schedule::where('id', $scheduleDetailData->sc_id)->first();
        $scheduleDateOldFormat = ScheduleDate::where('id', $scheduleData->sd_id)->first()->sd_date;
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat)->format('Ymd');

        if($scheduleData['sc_available'] == 0 || $scheduleDetailData['scd_available'] == 0) {
            return redirect()->route('old-bpjs')->with('error', 'Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
            return redirect()->route('old-bpjs')->with('error', 'Kuota Online Pasien Umum / Asuransi [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Terpenuhi.');
        }

        if(BpjsKesehatanAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($scheduleDateOldFormat) {
            $query->where('sd_date', $scheduleDateOldFormat);
        })->where('bap_norm', $this->normConverter->normConverter($patientTemporary['pt_norm']))->exists()
        ) {
            return redirect()->route('old-bpjs')->with('error', 'Anda Sudah Terdaftar Sebagai Pasien BPJS Untuk Tanggal ' . Carbon::createFromFormat('Ymd', $scheduleDate)->isoFormat('DD MMMM YYYY'));
        }

        $requestData = [
            'HealthcareID' => '001',
            'DepartmentID' => 'OUTPATIENT',
            'AppointmentMethod' => '003',
            'MedicalNo' => $this->normConverter->normConverter($patientTemporary['pt_norm']),
            'ServiceUnitCode' => $scheduleData['sc_clinic_code'],
            'ParamedicCode' => $scheduleData['sc_doctor_code'],
            'VisitTypeCode' => 'VT01',
            'OperationalTimeCode' => $scheduleData['sc_operational_time_code'],
            'StartDate' => $scheduleDate,
            'Session' => $scheduleDetailData->scd_session,
            'Notes' => '',
            'IsPersonalPayer' => 0,
            'BusinessPartnerCode' => 'BP00001',
            'ContractNo' => '114/HP-PKS-RSCK/XII/2021',
            'IsBPJS' => 1,
            'IsNewPatient' => 0,
            'UserID' => '197317247'
        ];

        $headers = $this->apiHeaderGenerator->generateApiHeader();
        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $client = new Client(['handler' => $handlerStack, 'verify' => false]);
            $response = $client->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/v2/centerback/ADT_A05_01", [
                'headers' => $headers,
                'form_params' => $requestData
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if($data['Status'] == 'SUCCESS (000)') {
                    $dataField = json_decode($data['Data'], true);
                    do {
                        $token = Str::random(6);
                        $tokenCheck = \App\Models\Appointment::where('scd_id', $scheduleDetailData['id'])->where('ap_token', $token)->exists();
                    } while ($tokenCheck);
                    $appointmentData = \App\Models\Appointment::create([
                        'scd_id' => $scheduleDetailData['id'],
                        'ap_ucode' => $dataField['AppointmentID'],
                        'ap_no' => $dataField['AppointmentNo'],
                        'ap_token' => strtoupper($token),
                        'ap_queue' => $dataField['QueueNo'],
                        'ap_type' => 'BPJS',
                        'ap_registration_time' => Carbon::createFromFormat('H:i', $dataField['StartTime'])->subMinutes(30),
                        'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataField['StartTime']),
                    ]);
                    BpjsKesehatanAppointment::create([
                        'ap_id' => $appointmentData['id'],
                        'bap_norm' => $dataField['MedicalNo'],
                        'bap_name' => $dataField['PatientName'],
                        'bap_birthday' => $patientTemporary['pt_birthday'],
                        'bap_gender' => $patientTemporary['pt_gender'],
                        'bap_phone' => $request->phone_number,
                        'bap_bpjs' => $patientTemporary['pt_bpjs'],
                        'bap_ppk1' => $patientTemporary['pt_ppk1'],
                    ]);

                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_bpjs');
                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_bpjs');

                    return redirect()->route('bpjs.final', $dataField['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                } else {
                    return redirect()->route('old-bpjs')->with('error', $data['Status'] . ' - ' . $data['Remarks']);
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
