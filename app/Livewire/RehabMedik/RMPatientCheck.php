<?php

namespace App\Livewire\RehabMedik;

use App\Models\AsuransiAppointment;
use App\Models\Clinic;
use App\Models\FisioterapiAppointment;
use App\Models\NewAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
use App\Services\APIBpjsHeaderGenerator;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use App\Services\AppointmentOpen;
use App\Services\FisioMaxAppointment;
use App\Services\NormConverter;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Livewire\Component;
use LZCompressor\LZString;

class RMPatientCheck extends Component
{
    public $patientStatus = null;
    public $norm, $birthday, $service, $phone_number, $nik, $address, $email;
    public $isInMedin, $serviceType, $patientData, $isInBpjs;
    public $selectedDate = null, $selectedClinic = null, $selectedDoctor = null, $selectedSession = null, $selectedBusinessPartner = null;
    public $dates = [], $clinics = [], $doctors = [], $sessions = [], $businessPartners = [];

    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;
    protected APIBpjsHeaderGenerator $apiBpjsHeaderGenerator;
    protected FisioMaxAppointment $fisioMaxAppointment;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate, APIBpjsHeaderGenerator $apiBpjsHeaderGenerator, FisioMaxAppointment $fisioMaxAppointment): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
        $this->apiBpjsHeaderGenerator = $apiBpjsHeaderGenerator;
        $this->fisioMaxAppointment = $fisioMaxAppointment;
    }

    public function render()
    {
        View::share('type', 'rehab-medik');
        return view('livewire.rehab-medik.rehab-patient-check', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ])->layout('frontend.layout', [
            'subTitle' => 'Form Pasien Rehab Medik & Fisioterapi',
            'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Rehab Medik & Fisioterapi',
            'subKeywords' => 'form pasien rehab medik, form pasien fisioterapi, form pasien'
        ]);
    }

    public function mount()
    {
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
                return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $response->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 1');
        }
    }

    public function updatedPatientStatus(): void
    {
        $this->reset(
            'selectedBusinessPartner',
            'selectedDate',
            'selectedClinic',
            'selectedDoctor',
            'selectedSession',
        );
    }

    public function updatedSelectedDate(): void
    {
        if ($this->selectedDate) {
            $selectedDateObject = $this->dates->firstWhere('id', $this->selectedDate);
            $isToday = \Carbon\Carbon::parse($selectedDateObject->sd_date)->isToday();
            if($this->patientStatus != 'lama-bpjs') {
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
                    ->whereIn('cl_code', ['KLI030', 'RHBKAM001'])
                    ->where('cl_active', true)
                    ->where('cl_umum', true)
                    ->orderBy('cl_name', 'ASC')
                    ->get();
            } else {
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
                    ->whereIn('cl_code', ['KLI030', 'KLI013'])
                    ->where('cl_active', true)
                    ->where('cl_umum', true)
                    ->orderBy('cl_name', 'ASC')
                    ->get();
            }
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

    public function createAppointmentOld()
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return back();
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($this->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $scheduleDetailData = ScheduleDetail::where('id', $this->selectedSession)->first();
        $scheduleData = Schedule::where('id', $scheduleDetailData->sc_id)->first();
        $scheduleDateOldFormat = ScheduleDate::where('id', $scheduleData->sd_id)->first()->sd_date;
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat)->format('Ymd');

        if($scheduleData['sc_available'] == 0 || $scheduleDetailData['scd_available'] == 0) {
            return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Kuota Online Pasien Rehabilitasi Medik [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Penuh. Silahkan Datang Langsung Ke Rumah Sakit Untuk Registrasi Langsung.');
            }
        }

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Ymd');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah. Contoh: 12/01/1990.');
        }

        $handlerStackCheck = HandlerStack::create();
        $handlerStackCheck->push(Middleware::retry(function ($retryCheck, $requestCheck, $responseCheck, $exceptionCheck) {
            return $retryCheck < 10 && $exceptionCheck instanceof RequestException && $exceptionCheck->getCode() === 28;
        }, function ($retryCheck) {
            return 1000 * pow(2, $retryCheck);
        }));

        try {
            $clientCheck = new Client(['handler' => $handlerStackCheck, 'verify' => false]);
            $responseCheck = $clientCheck->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/patient/{$medicalNo}", [
                'headers' => $headers,
            ]);
            if ($responseCheck->getStatusCode() == 200) {
                $dataCheck = json_decode($responseCheck->getBody(), true);
                if (isset($dataCheck['Data'])) {
                    $dataFieldCheck = json_decode($dataCheck['Data'], true);
                    if ($birthdate == $dataFieldCheck['DateOfBirth']) {
                        if($this->patientStatus == 'lama-umum' || $this->patientStatus == 'lama-bpjs') {
                            $requestData = [
                                'HealthcareID' => '001',
                                'DepartmentID' => 'OUTPATIENT',
                                'AppointmentMethod' => '003',
                                'MedicalNo' => $dataFieldCheck['MedicalNo'],
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
                            for($x = 0; $x < count($this->businessPartners); $x++) {
                                if($this->businessPartners[$x]['bp_code'] == $this->selectedBusinessPartner) {
                                    $businessPartnerData = [
                                        'bp_code' => $this->businessPartners[$x]['bp_code'],
                                        'bp_name' => $this->businessPartners[$x]['bp_name'],
                                        'bp_contract' => $this->businessPartners[$x]['bp_contract']
                                    ];
                                }
                            }

                            $requestData = [
                                'HealthcareID' => '001',
                                'DepartmentID' => 'OUTPATIENT',
                                'AppointmentMethod' => '003',
                                'MedicalNo' => $dataFieldCheck['MedicalNo'],
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

                        $handlerStackApp = HandlerStack::create();
                        $handlerStackApp->push(Middleware::retry(function ($retryApp, $requestApp, $responseApp, $exceptionApp) {
                            return $retryApp < 10 && $exceptionApp instanceof RequestException && $exceptionApp->getCode() === 28;
                        }, function ($retryApp) {
                            return 1000 * pow(2, $retryApp);
                        }));

                        try {
                            $clientApp = new Client(['handler' => $handlerStackApp, 'verify' => false]);
                            $responseApp = $clientApp->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/v2/centerback/ADT_A05_01", [
                                'headers' => $headers,
                                'form_params' => $requestData
                            ]);
                            if ($responseApp->getStatusCode() == 200) {
                                $dataApp = json_decode($responseApp->getBody(), true);
                                if($dataApp['Status'] == 'SUCCESS (000)') {
                                    $dataFieldApp = json_decode($dataApp['Data'], true);
                                    do {
                                        $tokenApp = Str::random(6);
                                        $tokenCheckApp = \App\Models\Appointment::where('scd_id', $scheduleDetailData['id'])->where('ap_token', $tokenApp)->exists();
                                    } while ($tokenCheckApp);
                                    if($this->patientStatus == 'lama-umum' || $this->patientStatus == 'lama-bpjs') {
                                        $appointmentData = \App\Models\Appointment::create([
                                            'scd_id' => $scheduleDetailData['id'],
                                            'ap_ucode' => $dataFieldApp['AppointmentID'],
                                            'ap_no' => $dataFieldApp['AppointmentNo'],
                                            'ap_token' => strtoupper($tokenApp),
                                            'ap_queue' => $dataFieldApp['QueueNo'],
                                            'ap_type' => 'UMUM',
                                            'ap_registration_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime'])->subMinutes(30),
                                            'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime']),
                                        ]);
                                        UmumAppointment::create([
                                            'ap_id' => $appointmentData['id'],
                                            'uap_norm' => $dataFieldApp['MedicalNo'],
                                            'uap_name' => $dataFieldApp['PatientName'],
                                            'uap_birthday' => $dataFieldCheck['DateOfBirth'],
                                            'uap_gender' => $dataFieldCheck['Gender'],
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

                                        if($this->patientStatus == 'lama-umum') {
                                            return redirect()->route('rehab-medik.umum.final', $dataFieldApp['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                                        } else {
                                            return redirect()->route('rehab-medik.bpjs.final', $dataFieldApp['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                                        }
                                    } else {
                                        $appointmentData = \App\Models\Appointment::create([
                                            'scd_id' => $scheduleDetailData['id'],
                                            'ap_ucode' => $dataFieldApp['AppointmentID'],
                                            'ap_no' => $dataFieldApp['AppointmentNo'],
                                            'ap_token' => strtoupper($tokenApp),
                                            'ap_queue' => $dataFieldApp['QueueNo'],
                                            'ap_type' => 'ASURANSI',
                                            'ap_registration_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime'])->subMinutes(30),
                                            'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime']),
                                        ]);
                                        AsuransiAppointment::create([
                                            'ap_id' => $appointmentData['id'],
                                            'aap_norm' => $dataFieldApp['MedicalNo'],
                                            'aap_name' => $dataFieldApp['PatientName'],
                                            'aap_birthday' => $dataFieldCheck['DateOfBirth'],
                                            'aap_gender' => $dataFieldCheck['Gender'],
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

                                        return redirect()->route('rehab-medik.asuransi.final', $dataFieldApp['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                                    }
                                } else {
                                    return redirect()->route('rehab-medik')->with('error', $dataApp['Status'] . ' - ' . $dataApp['Remarks']);
                                }
                            } else {
                                return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $responseApp->getStatusCode() . ']');
                            }
                        } catch (RequestException $e) {
                            return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 2');
                        }
                    } else {
                        return back()->with('error', 'Data Pasien Tidak Sesuai. Silahkan Cek Kembali Nomor Rekam Medis / NIK Pasien.');
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan. Silahkan Cek Kembali Nomor Rekam Medis / NIK Pasien.');
                }
            } else {
                return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $responseCheck->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 3');
        }
    }

    public function createAppointmentNew()
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return back();
        }

        $link = env('API_KEY', 'rsck');
        $headers = $this->apiHeaderGenerator->generateApiHeader();
        $headerBpjs = $this->apiBpjsHeaderGenerator->generateApiBpjsHeader();

        $scheduleDetailData = ScheduleDetail::where('id', $this->selectedSession)->first();
        $scheduleData = Schedule::where('id', $scheduleDetailData->sc_id)->first();
        $scheduleDateOldFormat = ScheduleDate::where('id', $scheduleData->sd_id)->first()->sd_date;
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat)->format('Ymd');

        if($scheduleData['sc_available'] == 0 || $scheduleDetailData['scd_available'] == 0) {
            return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Kuota Online Pasien Rehabilitasi Medik [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Penuh. Silahkan Datang Langsung Ke Rumah Sakit Untuk Registrasi Langsung.');
            }
        }

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Ymd');
            $birthdateBpjs = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Y-m-d');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah. Contoh: 12/01/1990.');
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
            $responseBpjs = $clientBpjs->get("https://apijkn.bpjs-kesehatan.go.id/vclaim-rest/peserta/nik/{$this->nik}/tglSEP/{$regBpjsDate}", [
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
                                return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih.');
                            }
                        } else {
                            break;
                        }
                    }

                    $bpjs_unCompressedResult = LZString::decompressFromEncodedURIComponent($bpjs_decryptResult);
                    $bpjs_result = json_decode($bpjs_unCompressedResult, TRUE);

                    if($bpjs_result['peserta']['mr']['noMR']) {
                        return back()->with('error', 'Mohon Maaf NIK Sudah Terdaftar di Sistem Kami dengan Nomor RM ' . $bpjs_result['peserta']['mr']['noMR'] . '. Silahkan Registrasi Sebagai Pasien Umum / Asuransi.');
                    } else if($bpjs_result['peserta']['tglLahir'] != $birthdateBpjs) {
                        return back()->with('error', 'NIK dan Tanggal Lahir Pasien Tidak Cocok. Silahkan Cek Kembali.');
                    } else {
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
                            'IdentityCardNo' => $bpjs_result['peserta']['nik'],
                            'PatientName' => $bpjs_result['peserta']['nama'],
                            'DateOfBirth' => Carbon::createFromFormat('Y-m-d', $bpjs_result['peserta']['tglLahir'])->format('Ymd'),
                            'Gender' => $bpjs_result['peserta']['sex'] == 'L' ? 'M' : 'F',
                            'Address' => $this->address,
                            'MobileNo' => $this->phone_number,
                            'EmailAddress' => $this->email
                        ];

                        $handlerStackApp = HandlerStack::create();
                        $handlerStackApp->push(Middleware::retry(function ($retryApp, $requestApp, $responseApp, $exceptionApp) {
                            return $retryApp < 10 && $exceptionApp instanceof RequestException && $exceptionApp->getCode() === 28;
                        }, function ($retryApp) {
                            return 1000 * pow(2, $retryApp);
                        }));

                        try {
                            $clientApp = new Client(['handler' => $handlerStackApp, 'verify' => false]);
                            $responseApp = $clientApp->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/v2/centerback/ADT_A05_01", [
                                'headers' => $headers,
                                'form_params' => $requestData
                            ]);

                            if ($responseApp->getStatusCode() == 200) {
                                $dataApp = json_decode($responseApp->getBody(), true);
                                if($dataApp['Status'] == 'SUCCESS (000)') {
                                    $checkNewAppointmentDuplicate = NewAppointment::where('nap_ssn', $requestData['IdentityCardNo'])->get();
                                    foreach ($checkNewAppointmentDuplicate as $checkData) {
                                        $checkAppointmentDuplicate = \App\Models\Appointment::where('id', $checkData['ap_id'])->where('scd_id', $scheduleDetailData['id'])->exists();
                                        if($checkAppointmentDuplicate) {
                                            return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf NIK Sudah Digunakan Untuk Pendaftaran Pasien Pada Klinik Dan Dokter Dari Yang Sama');
                                        }
                                    }

                                    $dataFieldApp = json_decode($dataApp['Data'], true);
                                    do {
                                        $token = Str::random(6);
                                        $tokenCheck = \App\Models\Appointment::where('scd_id', $scheduleDetailData['id'])->where('ap_token', $token)->exists();
                                    } while ($tokenCheck);
                                    $appointmentData = \App\Models\Appointment::create([
                                        'scd_id' => $scheduleDetailData['id'],
                                        'ap_ucode' => $dataFieldApp['AppointmentID'],
                                        'ap_no' => $dataFieldApp['AppointmentNo'],
                                        'ap_token' => strtoupper($token),
                                        'ap_queue' => $dataFieldApp['QueueNo'],
                                        'ap_type' => 'BARU',
                                        'ap_registration_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime'])->subMinutes(30),
                                        'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime']),
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

                                    return redirect()->route('rehab-medik.baru.final', $dataFieldApp['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                                } else {
                                    return redirect()->route('rehab-medik')->with('error', $dataApp['Status'] . ' - ' . $dataApp['Remarks']);
                                }
                            } else {
                                return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $responseApp->getStatusCode() . ']');
                            }
                        } catch (RequestException $e) {
                            return redirect()->route('rehab-medik')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 4');
                        }
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan. Silahkan Cek Kembali Nomor Rekam Medis / NIK Pasien.');
                }
            } else {
                return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $responseBpjs->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 5');
        }
    }

    public function createAppointmentFisio()
    {
        if(!$this->appointmentOpen->selectAppointmentOpen()) {
            return back();
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($this->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Ymd');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah. Contoh: 12/01/1990.');
        }

        $selectedDateFormat = ScheduleDate::where('sd_ucode', $this->selectedDate)->first();
        $selectedDateNumber = Carbon::createFromFormat('Y-m-d', $selectedDateFormat['sd_date'])->format('N');

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
                    $existingAppointment = FisioterapiAppointment::where('sd_id', $selectedDateFormat['id'])->where('fap_norm', $dataField['MedicalNo'])->first();

                    if($existingAppointment){
                        return back()->with('error', 'Mohon Maaf Pasien Dengan NORM ' . $dataField['MedicalNo'] . ' Sudah Terdaftar Pada Fisioterapi Tanggal ' . Carbon::createFromFormat('Y-m-d', $selectedDateFormat['sd_date'])->isoFormat('dddd, DD MMMM YYYY'));
                    }

                    if ($birthdate == $dataField['DateOfBirth']) {
                        $checkNorm = FisioterapiAppointment::where('sd_id', $selectedDateFormat['id'])->where('fap_type', $this->patientStatus)->where('fap_norm', $dataField['MedicalNo'])->count();
                        if($checkNorm == 0) {
                            do {
                                $ucode = Str::random(20);
                                $ucodeCheck = FisioterapiAppointment::where('fap_ucode', $ucode)->exists();
                            } while ($ucodeCheck);
                            do {
                                $token = Str::random(6);
                                $tokenCheck = FisioterapiAppointment::where('fap_token', $token)->exists();
                            } while ($tokenCheck);

                            $maxPatients = $this->fisioMaxAppointment->getMaxPatients($selectedDateNumber, $this->patientStatus);
                            $currentPatients = FisioterapiAppointment::where('sd_id', $selectedDateFormat['id'])->where('fap_type', $this->patientStatus)->count();
                            if($currentPatients < $maxPatients) {
                                if (($this->patientStatus === 'UMUM PAGI' || $this->patientStatus === 'BPJS PAGI')) {
                                    $reg_time = Carbon::createFromFormat('H:i', '07:00')->addMinutes((7 * ($currentPatients)))->subMinutes(30)->format('H:i');
                                    $app_time = Carbon::createFromFormat('H:i', '07:00')->addMinutes((7 * ($currentPatients)))->format('H:i');
                                } else {
                                    if ($selectedDateNumber == 6) {
                                        $reg_time = Carbon::createFromFormat('H:i', '12:00')->addMinutes((7 * ($currentPatients)))->subMinutes(30)->format('H:i');
                                        $app_time = Carbon::createFromFormat('H:i', '12:00')->addMinutes((7 * ($currentPatients)))->format('H:i');
                                    } else {
                                        $reg_time = Carbon::createFromFormat('H:i', '14:00')->addMinutes((7 * ($currentPatients)))->subMinutes(30)->format('H:i');
                                        $app_time = Carbon::createFromFormat('H:i', '14:00')->addMinutes((7 * ($currentPatients)))->format('H:i');
                                    }
                                }
                            } else {
                                return back()->with('error', 'Mohon Maaf Kuota Untuk Tanggal ' . Carbon::createFromFormat('Y-m-d', $selectedDateFormat['sd_date'])->isoFormat('dddd, DD MMMM YYYY') . ' Sudah Terpenuhi');
                            }

                            FisioterapiAppointment::create([
                                'sd_id' => $selectedDateFormat['id'],
                                'fap_ucode' => $ucode,
                                'fap_token' => strtoupper($token),
                                'fap_type' => $this->patientStatus,
                                'fap_queue' => $currentPatients + 1,
                                'fap_registration_time' => $reg_time,
                                'fap_appointment_time' => $app_time,
                                'fap_norm' => $dataField['MedicalNo'],
                                'fap_name' => $dataField['FullName'],
                                'fap_birthday' => Carbon::createFromFormat('Ymd', $dataField['DateOfBirth'])->format('Y-m-d'),
                                'fap_gender' => $dataField['Gender'],
                                'fap_phone' => $this->phone_number
                            ]);
                            return redirect()->route('rehab-medik.fisio.final', $ucode)->with('success', 'Registrasi Berhasil Dilakukan');
                        } else {
                            return back()->with('error', 'Mohon Maaf Pasien Dengan NORM ' . $dataField['MedicalNo'] . ' Sudah Terdaftar Pada Fisioterapi Tanggal ' . Carbon::createFromFormat('Y-m-d', $selectedDateFormat['sd_date'])->isoFormat('dddd, DD MMMM YYYY'));
                        }
                    } else {
                        return back()->with('error', 'Data Pasien Tidak Sesuai. Silahkan Cek Kembali Nomor Rekam Medis Pasien.');
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan. Silahkan Cek Kembali Nomor Rekam Medis  Pasien.');
                }
            } else {
                return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $response->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 6');
        }
    }
}
