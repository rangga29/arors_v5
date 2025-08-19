<?php

namespace App\Livewire\Baru;

use App\Models\Clinic;
use App\Models\NewAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component;
use function redirect;

class BaruAppointment extends Component
{
    public $patientData, $address, $phone_number, $email;
    public $selectedDate = null, $selectedClinic = null, $selectedDoctor = null, $selectedSession = null;
    public $dates = [], $clinics = [], $doctors = [], $sessions = [];

    protected APIHeaderGenerator $apiHeaderGenerator;
    protected AppointmentDate $appointmentDate;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, AppointmentDate $appointmentDate): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->appointmentDate = $appointmentDate;
    }

    public function render()
    {
        View::share('type', 'baru');
        return view('livewire.baru.baru-appointment')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Pasien Baru',
                'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Baru',
                'subKeywords' => 'form pasien baru, form pasien'
            ]);
    }

    public function mount($patientData): void
    {
        $this->patientData = $patientData;
        $this->dates = $this->appointmentDate->selectNextSevenAppointmentDates();
    }

    public function updatedSelectedDate(): void
    {
        if ($this->selectedDate) {
            $selectedDateObject = $this->dates->firstWhere('id', $this->selectedDate);
            $isToday = \Carbon\Carbon::parse($selectedDateObject->sd_date)->isToday();

            $this->clinics = Clinic::whereHas('schedules', function ($query) use ($isToday) {
                $query->where('sd_id', $this->selectedDate)
                    ->where('sc_available', true)
                    ->whereHas('scheduleDetails', function ($subQuery) use ($isToday) {
                        $subQuery->where('scd_umum', true)
                            ->where('scd_available', true)
                            //->whereRaw('scd_online_umum > scd_counter_online_umum');
                            ->where('scd_online_umum', '!=', 0);
                        if ($isToday) {
                            $subQuery->where('scd_start_time', '>=', now()->toTimeString());
                        }
                    });
            })
                ->where('cl_active', true)
                ->where('cl_umum', true)
                ->orderBy('cl_name', 'ASC')
                ->get();
        } else {
            $this->clinics = collect();
        }
        $this->reset(['selectedClinic', 'selectedDoctor', 'selectedSession']);
    }

    public function updatedSelectedClinic(): void
    {
        $selectedDateObject = $this->dates->firstWhere('id', $this->selectedDate);
        $isToday = $selectedDateObject && \Carbon\Carbon::parse($selectedDateObject->sd_date)->isToday();

        $this->doctors = Schedule::where('sd_id', $this->selectedDate)
            ->where('sc_clinic_code', $this->selectedClinic)
            ->where('sc_available', true)
            ->whereHas('scheduleDetails', function ($query) use ($isToday) {
                $query->where('scd_umum', true)
                    ->where('scd_available', true)
                    //->whereRaw('scd_online_umum > scd_counter_online_umum');
                    ->where('scd_online_umum', '!=', 0);
                if ($isToday) {
                    $query->where('scd_start_time', '>=', now()->toTimeString());
                }
            })
            ->with('scheduleDate')
            ->get();
        $this->reset(['selectedDoctor', 'selectedSession']);
    }

    public function updatedSelectedDoctor(): void
    {
        $selectedDateObject = $this->dates->firstWhere('id', $this->selectedDate);
        $isToday = $selectedDateObject && \Carbon\Carbon::parse($selectedDateObject->sd_date)->isToday();

        $query = ScheduleDetail::whereHas('schedule', function ($query) {
            $query->where('sd_id', $this->selectedDate)
                ->where('sc_doctor_code', $this->selectedDoctor)
                ->where('sc_clinic_code', $this->selectedClinic)
                ->where('sc_available', true);
        })
            ->where('scd_umum', true)
            ->where('scd_available', true);

        if ($isToday) {
            $query->where('scd_start_time', '>=', now()->toTimeString());
        }

        $this->sessions = $query->get();
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
            return redirect()->route('baru')->with('error', 'Mohon Maaf Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('baru')->with('error', 'Mohon Maaf Kuota Online Pasien [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Penuh. Silahkan Datang Langsung Ke Rumah Sakit Untuk Registrasi Langsung.');
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
                            return redirect()->route('baru')->with('error', 'Mohon Maaf NIK Sudah Digunakan Untuk Pendaftaran Pasien Pada Klinik Dan Dokter Dari Yang Sama');
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

                    return redirect()->route('baru.final', $dataField['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                } else {
                    return redirect()->route('baru')->with('error', $data['Status'] . ' - ' . $data['Remarks']);
                }
            } else {
                return redirect()->route('baru')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $response->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return redirect()->route('baru')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500]');
        }
    }
}
