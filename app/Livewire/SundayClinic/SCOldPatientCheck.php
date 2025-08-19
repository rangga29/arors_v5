<?php

namespace App\Livewire\SundayClinic;

use App\Services\APIHeaderGenerator;
use App\Services\AppointmentOpen;
use App\Services\NormConverter;
use App\Services\SCAppointmentDate;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class SCOldPatientCheck extends Component
{
    public $norm, $birthday, $service;
    public $isInMedin, $serviceType, $patientData;
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected AppointmentOpen $appointmentOpen;
    protected SCAppointmentDate $scAppointmentDate;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, AppointmentOpen $appointmentOpen, SCAppointmentDate $scAppointmentDate): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->appointmentOpen = $appointmentOpen;
        $this->scAppointmentDate = $scAppointmentDate;
    }

    public function render()
    {
        View::share('type', 'sunday-clinic');
        return view('livewire.old-sunday-clinic.osc-patient-check', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->scAppointmentDate->selectSCAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ])->layout('frontend.layout', [
            'subTitle' => 'Form Pasien Sunday Clinic',
            'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Sunday Clinic',
            'subKeywords' => 'form pasien klinik mingguan, form pasien, pasien klinik mingguan'
        ]);
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
                        $this->isInMedin = true;
                        $this->patientData = $dataField;
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
}
