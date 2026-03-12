<?php

namespace App\Livewire\Bpjs;

use App\Models\BpjsKesehatanAppointment;
use App\Models\Clinic;
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
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component;
use function redirect;

class BpjsAppointment extends Component
{
    public $patientData, $bpjsData, $phone_number;
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
        View::share('type', 'bpjs');
        return view('livewire.bpjs.bpjs-appointment')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Pasien BPJS',
                'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien BPJS',
                'subKeywords' => 'form pasien bpjs, form pasien'
            ]);
    }

    public function mount($patientData, $bpjsData): void
    {
        $this->patientData = $patientData;
        $this->bpjsData = $bpjsData;
        $this->dates = ScheduleDate::where('sd_date', $this->appointmentDate->selectAppointmentDate())->get();
        //$this->dates = ScheduleDate::where('sd_date', '>=', Carbon::today()->addDay()->format('Y-m-d'))->where('sd_date', '<=', Carbon::today()->addWeek()->format('Y-m-d'))->get();
        //$this->dates = ScheduleDate::where('sd_date', Carbon::today()->format('Y-m-d'))->get();
        $this->clinics = Clinic::where('cl_active', true)
            ->where('cl_bpjs', true)
            ->where('cl_code_bpjs', $this->bpjsData['poliRujukan']['kode'])
            ->orderBy('cl_order', 'ASC')
            ->get();
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
            ->where('scd_bpjs', true)
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
            return redirect()->route('bpjs')->with('error', 'Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
            return redirect()->route('bpjs')->with('error', 'Kuota Online Pasien Umum / Asuransi [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Terpenuhi.');
        }

        if(BpjsKesehatanAppointment::whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($scheduleDateOldFormat) {
                $query->where('sd_date', $scheduleDateOldFormat);
            })->where('bap_norm', $this->patientData['MedicalNo'])->exists()
        ) {
            return redirect()->route('bpjs')->with('error', 'Anda Sudah Terdaftar Sebagai Pasien BPJS Untuk Tanggal ' . Carbon::createFromFormat('Ymd', $scheduleDate)->isoFormat('DD MMMM YYYY'));
        }

        $requestData = [
            'HealthcareID' => '001',
            'DepartmentID' => 'OUTPATIENT',
            'AppointmentMethod' => '003',
            'MedicalNo' => $this->patientData['MedicalNo'],
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
                        'bap_birthday' => $this->patientData['DateOfBirth'],
                        'bap_gender' => $this->patientData['Gender'],
                        'bap_phone' => $this->phone_number,
                        'bap_bpjs' => $this->bpjsData['peserta']['noKartu'],
                        'bap_ppk1' => $this->bpjsData['noKunjungan'],
                    ]);

                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_bpjs');
                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_bpjs');

                    return redirect()->route('bpjs.final', $dataField['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                } else {
                    return redirect()->route('bpjs')->with('error', $data['Status'] . ' - ' . $data['Remarks']);
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
