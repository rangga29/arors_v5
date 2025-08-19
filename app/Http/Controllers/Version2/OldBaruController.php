<?php

namespace App\Http\Controllers\Version2;

use App\Http\Controllers\Controller;
use App\Models\NewAppointment;
use App\Models\PatientTemporary;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Services\APIBpjsHeaderGenerator;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use App\Services\AppointmentOpen;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LZCompressor\LZString;

class OldBaruController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected APIBpjsHeaderGenerator $apiBpjsHeaderGenerator;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator, APIBpjsHeaderGenerator $apiBpjsHeaderGenerator, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate)
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->apiBpjsHeaderGenerator = $apiBpjsHeaderGenerator;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
    }

    public function index()
    {
        return view('version2.baru-1', [
            'background' => 'baru',
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
        $headers = $this->apiHeaderGenerator->generateApiHeader();
        $headerBpjs = $this->apiBpjsHeaderGenerator->generateApiBpjsHeader();

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $request->birthday)->format('Ymd');
            $birthdateBpjs = Carbon::createFromFormat('d/m/Y', $request->birthday)->format('Y-m-d');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah');
        }
        $regBpjsDate = Carbon::today()->format('Y-m-d');

        $handlerStackBpjs = HandlerStack::create();
        $handlerStackBpjs->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $clientBpjs = new Client(['handler' => $handlerStackBpjs, 'verify' => false]);
            $responseBpjs = $clientBpjs->get("https://apijkn.bpjs-kesehatan.go.id/vclaim-rest/peserta/nik/{$request->nik}/tglSEP/{$regBpjsDate}", [
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

                    if($bpjs_result['peserta']['mr']['noMR']) {
                        return back()->with('error', 'NIK Sudah Terdaftar di Sistem Kami dengan NORM ' . $bpjs_result['peserta']['mr']['noMR'] . '. Silahkan Registrasi Sebagai Pasien Umum / Kontraktor.');
                    } else if($bpjs_result['peserta']['tglLahir'] != $birthdateBpjs) {
                        return back()->with('error', 'Tanggal Lahir Tidak Cocok.');
                    } else {
                        if($bpjs_result['peserta']['sex'] == 'L') {
                            $bpjs_gender = 'M^Laki-Laki';
                        } else {
                            $bpjs_gender = 'F^Perempuan';
                        }
                        do {
                            $token = Str::random(20);
                            $tokenCheck = PatientTemporary::where('pt_ucode', $token)->exists();
                        } while ($tokenCheck);
                        $patient_temp = PatientTemporary::create([
                            'pt_ucode' => $token,
                            'pt_norm' => '00-00-00-00',
                            'pt_name' => $bpjs_result['peserta']['nama'],
                            'pt_birthday' => $bpjs_result['peserta']['tglLahir'],
                            'pt_gender' => $bpjs_gender,
                            'pt_ssn' => $bpjs_result['peserta']['nik'],
                            'pt_bpjs' => $bpjs_result['peserta']['noKartu']
                        ]);
                        return redirect()->route('old-baru.appointment-create', $patient_temp['pt_ucode']);
                    }
                } else {
                    return back()->with('error', 'Data Salah Atau Tidak Ditemukan.');
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $responseBpjs->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function appointmentCreate(PatientTemporary $patientTemporary)
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return redirect()->route('old-baru');
        }

        $appointmentDate = $this->appointmentDate->selectAppointmentDate();

        $clinicsWithDoctors = Schedule::query()
            ->where('sc_available', true)
            ->whereHas('scheduleDate', function ($query) use ($appointmentDate) {
                $query->where('sd_date', $appointmentDate);
            })
            ->join('clinics', 'schedules.sc_clinic_code', '=', 'clinics.cl_code')
            ->join('schedule_details', 'schedules.id', '=', 'schedule_details.sc_id')
            ->where('clinics.cl_active', true)
            ->where('clinics.cl_umum', true)
            ->where('schedule_details.scd_available', true)
            ->where('schedule_details.scd_umum', true)
            ->orderBy('clinics.cl_order')
            ->get()
            ->groupBy('sc_clinic_name');

        return view('version2.baru-2', [
            'background' => 'baru',
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
            return redirect()->route('old-umum')->with('error', 'Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('old-umum')->with('error', 'Kuota Online Pasien Umum / Asuransi [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Terpenuhi. Silahkan Datang Langsung Ke Rumah Sakit Untuk Pendaftaran Langsung.');
            }
        }

        $requestData = [
            'HealthcareID' => '001',
            'DepartmentID' => 'OUTPATIENT',
            'AppointmentMethod' => '003',
            'ServiceUnitCode' => $scheduleData['sc_clinic_code'],
            'ParamedicCode' => $scheduleData['sc_doctor_code'],
            'VisitTypeCode' => 'VT01',
            'OperationalTimeCode' => $scheduleData['sc_operational_time_code'],
            'StartDate' => $scheduleDate,
            'Session' => $scheduleDetailData->scd_session,
            'Notes' => '',
            'IsPersonalPayer' => 1,
            'BusinessPartnerCode' => 'PERSONAL',
            'IsBPJS' => 0,
            'IsNewPatient' => 1,
            'UserID' => '197317247',
            'IdentityNoType' => 'X097^001',
            'IdentityCardNo' => $patientTemporary['pt_ssn'],
            'PatientName' => $patientTemporary['pt_name'],
            'DateOfBirth' => Carbon::createFromFormat('Y-m-d', $patientTemporary['pt_birthday'])->format('Ymd'),
            'Gender' => $patientTemporary['pt_gender'] == 'M^Laki-Laki' ? 'M' : 'F',
            'Address' => $request['address'],
            'MobileNo' => $request['phone_number'],
            'EmailAddress' => $request['email']
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
                    $checkNewAppointmentDuplicate = NewAppointment::where('nap_ssn', $requestData['IdentityCardNo'])->get();
                    foreach ($checkNewAppointmentDuplicate as $checkData) {
                        $checkAppointmentDuplicate = \App\Models\Appointment::where('id', $checkData['ap_id'])->where('scd_id', $scheduleDetailData['id'])->exists();
                        if($checkAppointmentDuplicate) {
                            return redirect()->route('baru')->with('error', 'NIK Sudah Digunakan Untuk Pendaftaran Pasien Baru Di Dokter Yang Sama');
                        }
                    }
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
                        'ap_type' => 'BARU',
                        'ap_registration_time' => Carbon::createFromFormat('H:i', $dataField['StartTime'])->subMinutes(30),
                        'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataField['StartTime']),
                    ]);
                    NewAppointment::create([
                        'ap_id' => $appointmentData['id'],
                        'nap_norm' => '00-00-00-00',
                        'nap_name' => $requestData['PatientName'],
                        'nap_birthday' => $requestData['DateOfBirth'],
                        'nap_phone' => $requestData['MobileNo'],
                        'nap_ssn' => $requestData['IdentityCardNo'],
                        'nap_gender' => $requestData['Gender'],
                        'nap_address' => $requestData['Address'],
                        'nap_email' => $requestData['EmailAddress']
                    ]);
                    if($scheduleDetailData['scd_counter_online_umum'] >= $scheduleDetailData['scd_online_umum'] && $scheduleDetailData['scd_counter_online_bpjs'] <= $scheduleDetailData['scd_online_bpjs'] ) {
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_max_bpjs');
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_online_bpjs');
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_max_umum');
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_online_umum');
                    }
                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_umum');
                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_umum');
                    return redirect()->route('baru.final', $dataField['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                } else {
                    return redirect()->route('old-baru')->with('error', $data['Status'] . ' - ' . $data['Remarks']);
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
