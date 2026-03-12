<?php

namespace App\Livewire\Fisio;

use App\Models\Clinic;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
use App\Models\AsuransiAppointment;
use App\Models\BpjsKesehatanAppointment;
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

class TWPatientCheck extends Component
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
        View::share('type', 'terapi-wicara');
        return view('livewire.fisio.tw-patient-check', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ])->layout('frontend.layout', [
            'subTitle' => 'Form Pasien Terapi Wicara',
            'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Terapi Wicara',
            'subKeywords' => 'form pasien terapi wicara, form pasien'
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
                        if ($dataField[$x]['IsActive'] && $dataField[$x]['ContractInfo'] != null && ($dataField[$x]['CustomerTypeDesc'] == 'Asuransi' || $dataField[$x]['CustomerTypeDesc'] == 'Perusahaan')) {
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
            if ($this->patientStatus == 'UMUM PAGI' || $this->patientStatus == 'ASURANSI PAGI') {
                $this->clinics = Clinic::whereHas('schedules', function ($query) use ($isToday) {
                    $query->where('sd_id', $this->selectedDate)
                        ->where('sc_available', true)
                        ->whereHas('scheduleDetails', function ($subQuery) use ($isToday) {
                            $subQuery->where('scd_umum', true)
                                ->where('scd_available', true)
                                ->where('scd_online_umum', '!=', 0);
                            if ($isToday) {
                                $subQuery->where('scd_start_time', '>=', now()->toTimeString());
                            }
                        });
                })
                    ->whereIn('cl_department', ['DIAGNOSTIC'])
                    ->where('cl_active', true)
                    ->where('cl_umum', true)
                    ->orderBy('cl_name', 'ASC')
                    ->get();
            } elseif ($this->patientStatus == 'UMUM SORE' || $this->patientStatus == 'ASURANSI SORE') {
                $this->clinics = Clinic::whereHas('schedules', function ($query) use ($isToday) {
                    $query->where('sd_id', $this->selectedDate)
                        ->where('sc_available', true)
                        ->whereHas('scheduleDetails', function ($subQuery) use ($isToday) {
                            $subQuery->where('scd_umum', true)
                                ->where('scd_available', true)
                                ->where('scd_online_umum', '!=', 0);
                            if ($isToday) {
                                $subQuery->where('scd_start_time', '>=', now()->toTimeString());
                            }
                        });
                })
                    ->whereIn('cl_department', ['DIAGNOSTIC'])
                    ->where('cl_active', true)
                    ->where('cl_umum', true)
                    ->orderBy('cl_name', 'ASC')
                    ->get();
            } elseif ($this->patientStatus == 'BPJS PAGI') {
                $this->clinics = Clinic::whereHas('schedules', function ($query) use ($isToday) {
                    $query->where('sd_id', $this->selectedDate)
                        ->where('sc_available', true)
                        ->whereHas('scheduleDetails', function ($subQuery) use ($isToday) {
                            $subQuery->where('scd_bpjs', true)
                                ->where('scd_available', true)
                                ->where('scd_online_bpjs', '!=', 0);
                            if ($isToday) {
                                $subQuery->where('scd_start_time', '>=', now()->toTimeString());
                            }
                        });
                })
                    ->whereIn('cl_department', ['DIAGNOSTIC'])
                    ->where('cl_active', true)
                    ->where('cl_bpjs', true)
                    ->orderBy('cl_name', 'ASC')
                    ->get();
            } else {
                $this->clinics = Clinic::whereHas('schedules', function ($query) use ($isToday) {
                    $query->where('sd_id', $this->selectedDate)
                        ->where('sc_available', true)
                        ->whereHas('scheduleDetails', function ($subQuery) use ($isToday) {
                            $subQuery->where('scd_bpjs', true)
                                ->where('scd_available', true)
                                ->where('scd_online_bpjs', '!=', 0);
                            if ($isToday) {
                                $subQuery->where('scd_start_time', '>=', now()->toTimeString());
                            }
                        });
                })
                    ->whereIn('cl_department', ['DIAGNOSTIC'])
                    ->where('cl_active', true)
                    ->where('cl_bpjs', true)
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

        if ($this->patientStatus == 'UMUM PAGI' || $this->patientStatus == 'ASURANSI PAGI') {
            $this->doctors = Schedule::where('sd_id', $this->selectedDate)
                ->where('sc_clinic_code', $this->selectedClinic)
                ->where('sc_doctor_code', 'FIS003')
                ->where('sc_available', true)
                ->whereHas('scheduleDetails', function ($query) use ($isToday) {
                    $query->where('scd_umum', true)
                        ->where('scd_available', true)
                        ->where('scd_online_umum', '!=', 0);
                    if ($isToday) {
                        $query->where('scd_start_time', '>=', now()->toTimeString());
                    }
                })
                ->with('scheduleDate')
                ->get();
            $this->reset(['selectedDoctor', 'selectedSession']);
        } elseif ($this->patientStatus == 'UMUM SORE' || $this->patientStatus == 'ASURANSI SORE') {
            $this->doctors = Schedule::where('sd_id', $this->selectedDate)
                ->where('sc_clinic_code', $this->selectedClinic)
                ->where('sc_doctor_code', 'FIS003')
                ->where('sc_available', true)
                ->whereHas('scheduleDetails', function ($query) use ($isToday) {
                    $query->where('scd_umum', true)
                        ->where('scd_available', true)
                        ->where('scd_online_umum', '!=', 0);
                    if ($isToday) {
                        $query->where('scd_start_time', '>=', now()->toTimeString());
                    }
                })
                ->with('scheduleDate')
                ->get();
            $this->reset(['selectedDoctor', 'selectedSession']);
        } elseif ($this->patientStatus == 'BPJS PAGI') {
            $this->doctors = Schedule::where('sd_id', $this->selectedDate)
                ->where('sc_clinic_code', $this->selectedClinic)
                ->where('sc_doctor_code', 'FIS003')
                ->where('sc_available', true)
                ->whereHas('scheduleDetails', function ($query) use ($isToday) {
                    $query->where('scd_bpjs', true)
                        ->where('scd_available', true)
                        ->where('scd_online_bpjs', '!=', 0);
                    if ($isToday) {
                        $query->where('scd_start_time', '>=', now()->toTimeString());
                    }
                })
                ->with('scheduleDate')
                ->get();
            $this->reset(['selectedDoctor', 'selectedSession']);
        } else {
            $this->doctors = Schedule::where('sd_id', $this->selectedDate)
                ->where('sc_clinic_code', $this->selectedClinic)
                ->where('sc_doctor_code', 'FIS003')
                ->where('sc_available', true)
                ->whereHas('scheduleDetails', function ($query) use ($isToday) {
                    $query->where('scd_bpjs', true)
                        ->where('scd_available', true)
                        ->where('scd_online_bpjs', '!=', 0);
                    if ($isToday) {
                        $query->where('scd_start_time', '>=', now()->toTimeString());
                    }
                })
                ->with('scheduleDate')
                ->get();
            $this->reset(['selectedDoctor', 'selectedSession']);
        }
    }

    public function updatedSelectedDoctor(): void
    {
        $selectedDateObject = $this->dates->firstWhere('id', $this->selectedDate);
        $isToday = $selectedDateObject && \Carbon\Carbon::parse($selectedDateObject->sd_date)->isToday();

        if ($this->patientStatus == 'UMUM PAGI' || $this->patientStatus == 'ASURANSI PAGI') {
            $query = ScheduleDetail::whereHas('schedule', function ($query) {
                $query->where('sd_id', $this->selectedDate)
                    ->where('sc_doctor_code', $this->selectedDoctor)
                    ->where('sc_clinic_code', $this->selectedClinic)
                    ->where('sc_available', true);
            })
                ->where('scd_start_time', '<', '12:00:00')
                ->where('scd_umum', true)
                ->where('scd_available', true);

            if ($isToday) {
                $query->where('scd_start_time', '>=', now()->toTimeString());
            }

            $this->sessions = $query->get();
            $this->reset(['selectedSession']);
        } elseif ($this->patientStatus == 'UMUM SORE' || $this->patientStatus == 'ASURANSI SORE') {
            $query = ScheduleDetail::whereHas('schedule', function ($query) {
                $query->where('sd_id', $this->selectedDate)
                    ->where('sc_doctor_code', $this->selectedDoctor)
                    ->where('sc_clinic_code', $this->selectedClinic)
                    ->where('sc_available', true);
            })
                ->where('scd_start_time', '>=', '12:00:00')
                ->where('scd_umum', true)
                ->where('scd_available', true);

            if ($isToday) {
                $query->where('scd_start_time', '>=', now()->toTimeString());
            }

            $this->sessions = $query->get();
            $this->reset(['selectedSession']);
        } elseif ($this->patientStatus == 'BPJS PAGI') {
            $query = ScheduleDetail::whereHas('schedule', function ($query) {
                $query->where('sd_id', $this->selectedDate)
                    ->where('sc_doctor_code', $this->selectedDoctor)
                    ->where('sc_clinic_code', $this->selectedClinic)
                    ->where('sc_available', true);
            })
                ->where('scd_start_time', '<', '12:00:00')
                ->where('scd_bpjs', true)
                ->where('scd_available', true);

            if ($isToday) {
                $query->where('scd_start_time', '>=', now()->toTimeString());
            }

            $this->sessions = $query->get();
            $this->reset(['selectedSession']);
        } else {
            $query = ScheduleDetail::whereHas('schedule', function ($query) {
                $query->where('sd_id', $this->selectedDate)
                    ->where('sc_doctor_code', $this->selectedDoctor)
                    ->where('sc_clinic_code', $this->selectedClinic)
                    ->where('sc_available', true);
            })
                ->where('scd_start_time', '>=', '12:00:00')
                ->where('scd_bpjs', true)
                ->where('scd_available', true);

            if ($isToday) {
                $query->where('scd_start_time', '>=', now()->toTimeString());
            }

            $this->sessions = $query->get();
            $this->reset(['selectedSession']);
        }
    }

    public function createAppointmentUmum()
    {
        if (!$this->appointmentOpen->selectAppointmentOpen()) {
            return back();
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($this->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $scheduleDetailData = ScheduleDetail::where('id', $this->selectedSession)->first();
        $scheduleData = Schedule::where('id', $scheduleDetailData->sc_id)->first();
        $scheduleDateOldFormat = ScheduleDate::where('id', $scheduleData->sd_id)->first()->sd_date;
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat)->format('Ymd');

        if ($scheduleData['sc_available'] == 0 || $scheduleDetailData['scd_available'] == 0) {
            return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if ($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if ($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Kuota Online Pasien Terapi Wicara [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Penuh. Silahkan Datang Langsung Ke Rumah Sakit Untuk Registrasi Langsung.');
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
                        $requestData = [
                            'HealthcareID' => '001',
                            'DepartmentID' => $scheduleData['sc_clinic_department'],
                            'AppointmentMethod' => '003',
                            'MedicalNo' => $dataFieldCheck['MedicalNo'],
                            'ServiceUnitCode' => $scheduleData['sc_clinic_code'],
                            'ParamedicCode' => $scheduleData['sc_doctor_code'],
                            'VisitTypeCode' => 'VT10',
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
                                if ($dataApp['Status'] == 'SUCCESS (000)') {
                                    $dataFieldApp = json_decode($dataApp['Data'], true);
                                    do {
                                        $tokenApp = Str::random(6);
                                        $tokenCheckApp = \App\Models\Appointment::where('scd_id', $scheduleDetailData['id'])->where('ap_token', $tokenApp)->exists();
                                    } while ($tokenCheckApp);

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

                                    if ($scheduleDetailData['scd_counter_online_umum'] >= $scheduleDetailData['scd_online_umum'] && $scheduleDetailData['scd_counter_online_bpjs'] <= $scheduleDetailData['scd_online_bpjs']) {
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_max_bpjs');
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_online_bpjs');
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_max_umum');
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_online_umum');
                                    }
                                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_umum');
                                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_umum');

                                    return redirect()->route('terapi-wicara.umum.final', $dataFieldApp['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                                } else {
                                    return redirect()->route('terapi-wicara')->with('error', $dataApp['Status'] . ' - ' . $dataApp['Remarks']);
                                }
                            } else {
                                return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $responseApp->getStatusCode() . ']');
                            }
                        } catch (RequestException $e) {
                            return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 2');
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

    public function createAppointmentAsuransi()
    {
        if (!$this->appointmentOpen->selectAppointmentOpen()) {
            return back();
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($this->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $scheduleDetailData = ScheduleDetail::where('id', $this->selectedSession)->first();
        $scheduleData = Schedule::where('id', $scheduleDetailData->sc_id)->first();
        $scheduleDateOldFormat = ScheduleDate::where('id', $scheduleData->sd_id)->first()->sd_date;
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat)->format('Ymd');

        if ($scheduleData['sc_available'] == 0 || $scheduleDetailData['scd_available'] == 0) {
            return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if ($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if ($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Kuota Online Pasien Terapi Wicara [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Penuh. Silahkan Datang Langsung Ke Rumah Sakit Untuk Registrasi Langsung.');
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
                        for ($x = 0; $x < count($this->businessPartners); $x++) {
                            if ($this->businessPartners[$x]['bp_code'] == $this->selectedBusinessPartner) {
                                $businessPartnerData = [
                                    'bp_code' => $this->businessPartners[$x]['bp_code'],
                                    'bp_name' => $this->businessPartners[$x]['bp_name'],
                                    'bp_contract' => $this->businessPartners[$x]['bp_contract']
                                ];
                            }
                        }

                        $requestData = [
                            'HealthcareID' => '001',
                            'DepartmentID' => $scheduleData['sc_clinic_department'],
                            'AppointmentMethod' => '003',
                            'MedicalNo' => $dataFieldCheck['MedicalNo'],
                            'ServiceUnitCode' => $scheduleData['sc_clinic_code'],
                            'ParamedicCode' => $scheduleData['sc_doctor_code'],
                            'VisitTypeCode' => 'VT10',
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
                                if ($dataApp['Status'] == 'SUCCESS (000)') {
                                    $dataFieldApp = json_decode($dataApp['Data'], true);
                                    do {
                                        $tokenApp = Str::random(6);
                                        $tokenCheckApp = \App\Models\Appointment::where('scd_id', $scheduleDetailData['id'])->where('ap_token', $tokenApp)->exists();
                                    } while ($tokenCheckApp);

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

                                    if ($scheduleDetailData['scd_counter_online_umum'] >= $scheduleDetailData['scd_online_umum'] && $scheduleDetailData['scd_counter_online_bpjs'] <= $scheduleDetailData['scd_online_bpjs']) {
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_max_bpjs');
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->decrement('scd_online_bpjs');
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_max_umum');
                                        ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_online_umum');
                                    }
                                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_umum');
                                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_umum');

                                    return redirect()->route('terapi-wicara.asuransi.final', $dataFieldApp['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                                } else {
                                    return redirect()->route('terapi-wicara')->with('error', $dataApp['Status'] . ' - ' . $dataApp['Remarks']);
                                }
                            } else {
                                return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $responseApp->getStatusCode() . ']');
                            }
                        } catch (RequestException $e) {
                            return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 2');
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

    public function createAppointmentBpjs()
    {
        if (!$this->appointmentOpen->selectAppointmentOpen()) {
            return back();
        }

        $link = env('API_KEY', 'rsck');
        $medicalNo = $this->normConverter->normConverter($this->norm);
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        $scheduleDetailData = ScheduleDetail::where('id', $this->selectedSession)->first();
        $scheduleData = Schedule::where('id', $scheduleDetailData->sc_id)->first();
        $scheduleDateOldFormat = ScheduleDate::where('id', $scheduleData->sd_id)->first()->sd_date;
        $scheduleDate = Carbon::createFromFormat('Y-m-d', $scheduleDateOldFormat)->format('Ymd');

        if ($scheduleData['sc_available'] == 0 || $scheduleDetailData['scd_available'] == 0) {
            return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Jadwal [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Tidak Tersedia');
        }

        if ($scheduleDetailData['scd_counter_online_umum'] === $scheduleDetailData['scd_online_umum']) {
            if ($scheduleDetailData['scd_counter_online_bpjs'] === $scheduleDetailData['scd_online_bpjs']) {
                return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Kuota Online Pasien Terapi Wicara [' . $scheduleData['sc_clinic_name'] . ' -- ' . $scheduleData['sc_doctor_name'] . '] Sudah Penuh. Silahkan Datang Langsung Ke Rumah Sakit Untuk Registrasi Langsung.');
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
                        $requestData = [
                            'HealthcareID' => '001',
                            'DepartmentID' => $scheduleData['sc_clinic_department'],
                            'AppointmentMethod' => '003',
                            'MedicalNo' => $dataFieldCheck['MedicalNo'],
                            'ServiceUnitCode' => $scheduleData['sc_clinic_code'],
                            'ParamedicCode' => $scheduleData['sc_doctor_code'],
                            'VisitTypeCode' => 'VT10',
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
                                if ($dataApp['Status'] == 'SUCCESS (000)') {
                                    $dataFieldApp = json_decode($dataApp['Data'], true);
                                    do {
                                        $tokenApp = Str::random(6);
                                        $tokenCheckApp = \App\Models\Appointment::where('scd_id', $scheduleDetailData['id'])->where('ap_token', $tokenApp)->exists();
                                    } while ($tokenCheckApp);

                                    $appointmentData = \App\Models\Appointment::create([
                                        'scd_id' => $scheduleDetailData['id'],
                                        'ap_ucode' => $dataFieldApp['AppointmentID'],
                                        'ap_no' => $dataFieldApp['AppointmentNo'],
                                        'ap_token' => strtoupper($tokenApp),
                                        'ap_queue' => $dataFieldApp['QueueNo'],
                                        'ap_type' => 'BPJS',
                                        'ap_registration_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime'])->subMinutes(30),
                                        'ap_appointment_time' => Carbon::createFromFormat('H:i', $dataFieldApp['StartTime']),
                                    ]);

                                    BpjsKesehatanAppointment::create([
                                        'ap_id' => $appointmentData['id'],
                                        'bap_norm' => $dataFieldApp['MedicalNo'],
                                        'bap_name' => $dataFieldApp['PatientName'],
                                        'bap_birthday' => $dataFieldCheck['DateOfBirth'],
                                        'bap_gender' => $dataFieldCheck['Gender'],
                                        'bap_phone' => $this->phone_number,
                                        'bap_bpjs' => '-',
                                        'bap_ppk1' => '-'
                                    ]);

                                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_max_bpjs');
                                    ScheduleDetail::where('id', $scheduleDetailData['id'])->increment('scd_counter_online_bpjs');

                                    return redirect()->route('terapi-wicara.bpjs.final', $dataFieldApp['AppointmentID'])->with('success', 'Registrasi Berhasil Dilakukan');
                                } else {
                                    return redirect()->route('terapi-wicara')->with('error', $dataApp['Status'] . ' - ' . $dataApp['Remarks']);
                                }
                            } else {
                                return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $responseApp->getStatusCode() . ']');
                            }
                        } catch (RequestException $e) {
                            return redirect()->route('terapi-wicara')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500] - 2');
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
}
