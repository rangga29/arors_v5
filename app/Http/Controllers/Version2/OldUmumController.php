<?php

namespace App\Http\Controllers\Version2;

use App\Http\Controllers\Controller;
use App\Models\PatientTemporary;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
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

class OldUmumController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate)
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
    }

    public function index()
    {
        return view('version2.umum-1', [
            'background' => 'umum',
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ]);
    }

    public function patientCheck(Request $request)
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return redirect()->route('old-umum');
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($request->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

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
                        do {
                            $token = Str::random(20);
                            $tokenCheck = PatientTemporary::where('pt_ucode', $token)->exists();
                        } while ($tokenCheck);
                        $patient_temp = PatientTemporary::create([
                            'pt_ucode' => $token,
                            'pt_norm' => $request->norm,
                            'pt_name' => $dataField['FullName'],
                            'pt_birthday' => Carbon::createFromFormat('Ymd', $dataField['DateOfBirth'])->format('Y-m-d'),
                            'pt_gender' => $dataField['Gender']
                        ]);
                        return redirect()->route('old-umum.appointment-create', $patient_temp['pt_ucode']);
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
            return redirect()->route('old-umum');
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

        return view('version2.umum-2', [
            'background' => 'umum',
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
            'MedicalNo' => $this->normConverter->normConverter($patientTemporary['pt_norm']),
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
                        'ap_type' => 'UMUM',
                        'ap_registration_time' => Carbon::createFromFormat('H:i', $dataField['StartTime'])->subMinutes(30),
                        'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataField['StartTime']),
                    ]);
                    UmumAppointment::create([
                        'ap_id' => $appointmentData['id'],
                        'uap_norm' => $dataField['MedicalNo'],
                        'uap_name' => $dataField['PatientName'],
                        'uap_birthday' => $patientTemporary['pt_birthday'],
                        'uap_gender' => $patientTemporary['pt_gender'],
                        'uap_phone' => $request->phone_number,
                    ]);

                    if($scheduleDetailData['scd_counter_online_umum'] >= $scheduleDetailData['scd_online_umum'] && $scheduleDetailData['scd_counter_online_bpjs'] <= $scheduleDetailData['scd_online_bpjs'] ) {
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_max_bpjs');
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_online_bpjs');
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_max_umum');
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_online_umum');
                    }
                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_umum');
                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_umum');

                    return redirect()->route('umum.final', $dataField['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                } else {
                    return redirect()->route('old-umum')->with('error', $data['Status'] . ' - ' . $data['Remarks']);
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
