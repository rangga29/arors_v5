<?php

namespace App\Livewire\SundayClinic;

use App\Models\Clinic;
use App\Models\NewAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Services\APIHeaderGenerator;
use App\Services\SCAppointmentDate;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component;
use function back;
use function env;
use function json_decode;
use function pow;
use function redirect;
use function strtoupper;

class SCNewAppointment extends Component
{
    public $patientData, $address, $phone_number, $email;
    public $selectedDate = null, $selectedClinic = null, $selectedDoctor = null, $selectedSession = null;
    public $dates = [], $clinics = [], $doctors = [], $sessions = [];
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected SCAppointmentDate $scAppointmentDate;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, SCAppointmentDate $scAppointmentDate): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->scAppointmentDate = $scAppointmentDate;
    }

    public function render()
    {
        View::share('type', 'sunday-clinic');
        return view('livewire.new-sunday-clinic.nsc-appointment')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Pasien Sunday Clinic',
                'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Sunday Clinic',
                'subKeywords' => 'form pasien klinik mingguan, form pasien, pasien klinik mingguan'
            ]);
    }

    public function mount($patientData): void
    {
        $this->patientData = $patientData;
        $this->dates = ScheduleDate::where('sd_date', $this->scAppointmentDate->selectSCAppointmentDate())->get();
        $this->clinics = Clinic::where('cl_code', 'KES001')->where('cl_active', true)->where('cl_umum', true)->orderBy('cl_order', 'ASC')->get();
    }

    public function updatedSelectedClinic(): void
    {
        $this->doctors = Schedule::where('sd_id', $this->selectedDate)
            ->where('sc_clinic_code', $this->selectedClinic)
            ->where('sc_available', true)
            ->with('scheduleDate')
            ->get();
        $this->reset(['selectedDoctor', 'selectedSession']);
    }

    public function updatedSelectedDoctor(): void
    {
        $this->sessions = ScheduleDetail::whereHas('schedule', function ($query) {
            $query->where('sd_id', $this->selectedDate)
                ->where('sc_doctor_code', $this->selectedDoctor)
                ->where('sc_clinic_code', $this->selectedClinic)
                ->where('sc_available', true);
        })
            ->where('scd_umum', true)
            ->where('scd_available', true)
            ->get();
        $this->selectedSession = null;
    }

    public function createAppointment()
    {
        $link = env('API_KEY', 'rsck');

        $scheduleDetailData = ScheduleDetail::where('id', $this->selectedSession)->first();
        $scheduleData = Schedule::where('id', $scheduleDetailData->sc_id)->first();
        $scheduleDateOldFormat = ScheduleDate::where('id', $scheduleData->sd_id)->first()->sd_date;
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat)->format('Ymd');

        if($scheduleData['sc_available'] == 0 || $scheduleDetailData['scd_available'] == 0) {
            return redirect()->route('umum')->with('error', 'Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('umum')->with('error', 'Kuota Online Pasien Umum / Asuransi [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Terpenuhi. Silahkan Datang Langsung Ke Rumah Sakit Untuk Pendaftaran Langsung.');
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
            'IdentityCardNo' => $this->patientData['nik'],
            'PatientName' => $this->patientData['nama'],
            'DateOfBirth' => Carbon::createFromFormat('Y-m-d', $this->patientData['tglLahir'])->format('Ymd'),
            'Gender' => $this->patientData['sex'] == 'L' ? 'M' : 'F',
            'Address' => $this->address,
            'MobileNo' => $this->phone_number,
            'EmailAddress' => $this->email
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
                    $checkNewAppointmentDuplicate = NewAppointment::where('nap_ssn', $requestData['IdentityCardNo'])->get();
                    foreach ($checkNewAppointmentDuplicate as $checkData) {
                        $checkAppointmentDuplicate = \App\Models\Appointment::where('id', $checkData['ap_id'])->where('scd_id', $scheduleDetailData['id'])->exists();
                        if($checkAppointmentDuplicate) {
                            return redirect()->route('baru')->with('error', 'NIK Sudah Digunakan Untuk Pendaftaran Pasien Baru Di Dokter Yang Sama');
                        }
                    }

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

                    return redirect()->route('sunday-clinic.new-patient.final', $dataField['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                } else {
                    return redirect()->route('sunday-clinic.new-patient')->with('error', $data['Status'] . ' - ' . $data['Remarks']);
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
