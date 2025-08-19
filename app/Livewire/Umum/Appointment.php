<?php

namespace App\Livewire\Umum;

use App\Models\AsuransiAppointment;
use App\Models\Clinic;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use App\Services\NormConverter;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component;

class Appointment extends Component
{
    public $patientData, $serviceType, $phone_number;
    public $selectedDate = null, $selectedClinic = null, $selectedDoctor = null, $selectedSession = null, $selectedBusinessPartner = null;
    public $dates = [], $clinics = [], $doctors = [], $sessions = [], $businessPartners = [];

    protected APIHeaderGenerator $apiHeaderGenerator;
    protected AppointmentDate $appointmentDate;
    protected NormConverter $normConverter;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, AppointmentDate $appointmentDate, NormConverter $normConverter): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->appointmentDate = $appointmentDate;
        $this->normConverter = $normConverter;
    }

    public function render()
    {
        View::share('type', 'umum');
        return view('livewire.umum.appointment')
            ->layout('frontend.layout', [
                'subTitle' => 'Form Pasien Umum / Kontraktor',
                'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Umum / Kontraktor',
                'subKeywords' => 'form pasien umum kontraktor, form pasien umum, form pasien asuransi, form pasien, form pasien kontraktor'
            ]);
    }

    public function mount($patientData, $serviceType)
    {
        $this->patientData = $patientData;
        $this->serviceType = $serviceType;
        $this->dates = $this->appointmentDate->selectNextSevenAppointmentDates();

        $responses = [];
        $link = env('API_KEY', 'rsck');
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 3 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $client = new Client(['handler' => $handlerStack, 'verify' => false]);
            $response = $client->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/businesspartners/payer/list", [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if (!empty($data['Data'])) {
                    $dataField = json_decode($data['Data'], true);
                    $businessPartners = [];
                    for ($x = 0; $x < count($dataField); $x++) {
                        if($dataField[$x]['IsActive'] && $dataField[$x]['ContractInfo'] != null && ($dataField[$x]['CustomerTypeDesc'] == 'Asuransi' || $dataField[$x]['CustomerTypeDesc'] == 'Perusahaan')) {
                            $businessPartner = [
                                'bp_code' => $dataField[$x]['BusinessPartnerCode'],
                                'bp_name' => $dataField[$x]['BusinessPartnerName'],
                                'bp_contract' => $dataField[$x]['ContractInfo'][0]['ContractNo']
                            ];
                            $businessPartners[] = $businessPartner;
                        }
                    }
                    $businessPartnersCollection = collect($businessPartners);
                    $sortedBusinessPartners = $businessPartnersCollection->sortBy('bp_name')->values()->all();
                    $this->businessPartners = $sortedBusinessPartners;
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
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
            return redirect()->route('umum')->with('error', 'Mohon Maaf Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('umum')->with('error', 'Mohon Maaf Kuota Online Pasien Umum / Asuransi [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Penuh. Silahkan Datang Langsung Ke Rumah Sakit Untuk Registrasi Langsung.');
            }
        }

        if($this->serviceType == 'umum') {
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
                'IsPersonalPayer' => 1,
                'BusinessPartnerCode' => 'PERSONAL',
                'IsBPJS' => 0,
                'IsNewPatient' => 0,
                'UserID' => '197317247'
            ];
        } else {
            $headers = $this->apiHeaderGenerator->generateApiHeader();
            $handlerStack = HandlerStack::create();
            $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
                return $retry < 3 && $exception instanceof RequestException && $exception->getCode() === 28;
            }, function ($retry) {
                return 1000 * pow(2, $retry);
            }));

            try {
                $client = new Client(['handler' => $handlerStack, 'verify' => false]);
                $response = $client->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/businesspartners/payer/list", [
                    'headers' => $headers,
                ]);
                if ($response->getStatusCode() == 200) {
                    $data = json_decode($response->getBody(), true);
                    if (!empty($data['Data'])) {
                        $dataField = json_decode($data['Data'], true);
                        $businessPartners = [];
                        for ($x = 0; $x < count($dataField); $x++) {
                            if($dataField[$x]['BusinessPartnerCode'] == $this->selectedBusinessPartner) {
                                $businessPartnerData = [
                                    'bp_code' => $dataField[$x]['BusinessPartnerCode'],
                                    'bp_name' => $dataField[$x]['BusinessPartnerName'],
                                    'bp_contract' => $dataField[$x]['ContractInfo'][0]['ContractNo']
                                ];
                            }
                        }
                    }
                } else {
                    return redirect()->route('umum')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $response->getStatusCode() . ']');
                }
            } catch (RequestException $e) {
                return redirect()->route('umum')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500]');
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
                'BusinessPartnerCode' => $businessPartnerData['bp_code'],
                'ContractNo' => $businessPartnerData['bp_contract'],
                'IsBPJS' => 0,
                'IsNewPatient' => 0,
                'UserID' => '197317247'
            ];
        }

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
                    if($this->serviceType == 'umum') {
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
                            'uap_birthday' => $this->patientData['DateOfBirth'],
                            'uap_gender' => $this->patientData['Gender'],
                            'uap_phone' => $this->phone_number,
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
                        $appointmentData = \App\Models\Appointment::create([
                            'scd_id' => $scheduleDetailData['id'],
                            'ap_ucode' => $dataField['AppointmentID'],
                            'ap_no' => $dataField['AppointmentNo'],
                            'ap_token' => strtoupper($token),
                            'ap_queue' => $dataField['QueueNo'],
                            'ap_type' => 'ASURANSI',
                            'ap_registration_time' => Carbon::createFromFormat('H:i', $dataField['StartTime'])->subMinutes(30),
                            'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataField['StartTime']),
                        ]);
                        AsuransiAppointment::create([
                            'ap_id' => $appointmentData['id'],
                            'aap_norm' => $dataField['MedicalNo'],
                            'aap_name' => $dataField['PatientName'],
                            'aap_birthday' => $this->patientData['DateOfBirth'],
                            'aap_gender' => $this->patientData['Gender'],
                            'aap_phone' => $this->phone_number,
                            'aap_business_partner_code' => $businessPartnerData['bp_code'],
                            'aap_business_partner_name' => $businessPartnerData['bp_name']
                        ]);

                        if($scheduleDetailData['scd_counter_online_umum'] >= $scheduleDetailData['scd_online_umum'] && $scheduleDetailData['scd_counter_online_bpjs'] <= $scheduleDetailData['scd_online_bpjs'] ) {
                            ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_max_bpjs');
                            ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_online_bpjs');
                            ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_max_umum');
                            ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_online_umum');
                        }
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_umum');
                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_umum');

                        return redirect()->route('asuransi.final', $dataField['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                    }
                } else {
                    if($data['Status'] == 'FAILED (102)') {
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
                            'IsPersonalPayer' => 1,
                            'BusinessPartnerCode' => 'PERSONAL',
                            'IsBPJS' => 0,
                            'IsNewPatient' => 0,
                            'UserID' => '197317247'
                        ];
                        $date = $scheduleDateOldFormat;
                        if($this->serviceType == 'umum') {
                            $umumAppointmentExists = UmumAppointment::with('appointment.scheduleDetail.schedule.scheduleDate')
                                ->where('uap_norm', $this->patientData['MedicalNo'])
                                ->whereDate('uap_birthday', $this->patientData['DateOfBirth'])
                                ->whereHas('appointment.scheduleDetail.schedule.scheduleDate', function ($query) use ($date) {
                                    $query->where('sd_date', $date);
                                })
                                ->whereHas('appointment.scheduleDetail.schedule', function ($query) use ($scheduleData['sc_clinic_code') {
                                    $query->where('sc_clinic_code', $scheduleData['sc_clinic_code'])->where('sc_doctor_code', $scheduleData['sc_doctor_code'])->where('scd_session', $scheduleDetailData->scd_session);
                                })
                                ->get();
                            dd($umumAppointmentExists, $date, $this->selectedClinic, $this->selectedDoctor, $this->selectedSession);
                        } else {

                        }
                        return redirect()->route('umum')->with('error1', $data['Remarks']);
                    } else {
                        return redirect()->route('umum')->with('error1', $data['Remarks']);
                    }
                }
            } else {
                return redirect()->route('umum')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $response->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return redirect()->route('umum')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500]');
        }
    }
}
