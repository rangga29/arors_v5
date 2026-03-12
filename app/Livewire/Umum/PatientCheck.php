<?php

namespace App\Livewire\Umum;

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
use Illuminate\Support\Facades\View;
use Livewire\Component;

class PatientCheck extends Component
{
    public $norm, $birthday, $service;
    public $isInMedin, $serviceType, $patientData;
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
    }

    public function render()
    {
        View::share('type', 'umum');
        return view('livewire.umum.patient-check', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ])->layout('frontend.layout', [
            'subTitle' => 'Form Pasien Umum / Kontraktor',
            'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien Umum / Kontraktor',
            'subKeywords' => 'form pasien umum kontraktor, form pasien umum, form pasien asuransi, form pasien, form pasien kontraktor'
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
            return back()->with('error', 'Format Tanggal Lahir Salah. Contoh: 12/01/1990.');
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
                        $this->serviceType = $this->service;
                        $this->patientData = $dataField;
                    } else {
                        return back()->with('error', 'Data Pasien Tidak Sesuai. Silahkan Cek Kembali Nomor Rekam Medis / NIK Pasien.');
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan. Silahkan Cek Kembali Nomor Rekam Medis / NIK Pasien.');
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
