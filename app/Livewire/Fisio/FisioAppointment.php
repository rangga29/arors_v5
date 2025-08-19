<?php

namespace App\Livewire\Fisio;

use App\Models\FisioterapiAppointment;
use App\Models\ScheduleDate;
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

class FisioAppointment extends Component
{
    public $norm, $birthday, $service, $phone_number;
    public $selectedDate = null;
    public $dates = [];

    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected FisioMaxAppointment $fisioMaxAppointment;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, FisioMaxAppointment $fisioMaxAppointment, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->fisioMaxAppointment = $fisioMaxAppointment;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
    }

    public function render()
    {
        View::share('type', 'fisioterapi');
        return view('livewire.fisio.fisio-appointment', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ])->layout('frontend.layout', [
            'subTitle' => 'Form Pasien Fisioterapi',
            'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Fisioterapi',
            'subKeywords' => 'form pasien fisioterapi, form pasien'
        ]);
    }

    public function mount(): void
    {
        $this->dates = $this->appointmentDate->selectNextSevenAppointmentDates();
    }

    public function checkPatient()
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
                        $checkNorm = FisioterapiAppointment::where('sd_id', $selectedDateFormat['id'])->where('fap_type', $this->service)->where('fap_norm', $dataField['MedicalNo'])->count();
                        if($checkNorm == 0) {
                            do {
                                $ucode = Str::random(20);
                                $ucodeCheck = FisioterapiAppointment::where('fap_ucode', $ucode)->exists();
                            } while ($ucodeCheck);
                            do {
                                $token = Str::random(6);
                                $tokenCheck = FisioterapiAppointment::where('fap_token', $token)->exists();
                            } while ($tokenCheck);

                            $maxPatients = $this->fisioMaxAppointment->getMaxPatients($selectedDateNumber, $this->service);
                            $currentPatients = FisioterapiAppointment::where('sd_id', $selectedDateFormat['id'])->where('fap_type', $this->service)->count();
                            if($currentPatients < $maxPatients) {
                                if (($this->service === 'UMUM PAGI' || $this->service === 'BPJS PAGI')) {
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
                                return back()->with('error', 'Mohon Maaf Kuota Fisioterapi Untuk Tanggal ' . Carbon::createFromFormat('Y-m-d', $selectedDateFormat['sd_date'])->isoFormat('dddd, DD MMMM YYYY') . ' Sudah Terpenuhi.');
                            }

                            FisioterapiAppointment::create([
                                'sd_id' => $selectedDateFormat['id'],
                                'fap_ucode' => $ucode,
                                'fap_token' => strtoupper($token),
                                'fap_type' => $this->service,
                                'fap_queue' => $currentPatients + 1,
                                'fap_registration_time' => $reg_time,
                                'fap_appointment_time' => $app_time,
                                'fap_norm' => $dataField['MedicalNo'],
                                'fap_name' => $dataField['FullName'],
                                'fap_birthday' => Carbon::createFromFormat('Ymd', $dataField['DateOfBirth'])->format('Y-m-d'),
                                'fap_gender' => $dataField['Gender'],
                                'fap_phone' => $this->phone_number
                            ]);
                            return redirect()->route('fisioterapi.final', $ucode)->with('success', 'Registrasi Berhasil Dilakukan');
                        } else {
                            return back()->with('error', 'Mohon Maaf Pasien Dengan NORM ' . $dataField['MedicalNo'] . ' Sudah Terdaftar Pada Fisioterapi Tanggal ' . Carbon::createFromFormat('Y-m-d', $selectedDateFormat['sd_date'])->isoFormat('dddd, DD MMMM YYYY'));
                        }
                    } else {
                        return back()->with('error', 'Data Pasien Tidak Sesuai. Silahkan Cek Kembali Nomor Rekam Medis Pasien.');
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan. Silahkan Cek Kembali Nomor Rekam Medis Pasien.');
                }
            } else {
                return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $response->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return back()->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500]');
        }
    }
}
