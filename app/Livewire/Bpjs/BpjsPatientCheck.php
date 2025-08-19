<?php

namespace App\Livewire\Bpjs;

use App\Services\APIBpjsHeaderGenerator;
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
use LZCompressor\LZString;

class BpjsPatientCheck extends Component
{
    public $norm, $birthday, $ppk1;
    public $isInMedin, $patientData, $bpjsData;
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected NormConverter $normConverter;
    protected APIBpjsHeaderGenerator $apiBpjsHeaderGenerator;
    protected AppointmentOpen $appointmentOpen;
    protected AppointmentDate $appointmentDate;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, NormConverter $normConverter, APIBpjsHeaderGenerator $apiBpjsHeaderGenerator, AppointmentOpen $appointmentOpen, AppointmentDate $appointmentDate): void
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->apiBpjsHeaderGenerator = $apiBpjsHeaderGenerator;
        $this->normConverter = $normConverter;
        $this->appointmentOpen = $appointmentOpen;
        $this->appointmentDate = $appointmentDate;
    }

    public function render()
    {
        View::share('type', 'bpjs');
        return view('livewire.bpjs.bpjs-patient-check', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
            'isOpen' => $this->appointmentOpen->selectAppointmentOpen(),
            'currentHour' => now()->hour
        ])->layout('frontend.layout', [
            'subTitle' => 'Form Pasien BPJS',
            'description' => 'Form Registrasi Online Rumah Sakit Cahya Kawaluyan untuk Pasien BPJS',
            'subKeywords' => 'form pasien bpjs, form pasien'
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
        $headerBpjs = $this->apiBpjsHeaderGenerator->generateApiBpjsHeader();

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

            if ($response->getStatusCode() == 200)
            {
                $data = json_decode($response->getBody(), true);
                if (isset($data['Data'])) {
                    $dataField = json_decode($data['Data'], true);
                    if ($birthdate == $dataField['DateOfBirth']) {
                        $handlerStackBpjs = HandlerStack::create();
                        $handlerStackBpjs->push(Middleware::retry(function ($retry, $request, $response, $exception) {
                            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
                        }, function ($retry) {
                            return 1000 * pow(2, $retry);
                        }));

                        do {
                            try {
                                $clientBpjs = new Client(['handler' => $handlerStackBpjs, 'verify' => false]);
                                $responseBpjs = $clientBpjs->get("https://apijkn.bpjs-kesehatan.go.id/vclaim-rest/rujukan/{$this->ppk1}", [
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
                                                    return back()->with('error', 'Terjadi Kesalahan. Silahkan Dicoba Kembali.');
                                                }
                                            } else {
                                                break;
                                            }
                                        }

                                        $bpjs_unCompressedResult = LZString::decompressFromEncodedURIComponent($bpjs_decryptResult);
                                        $bpjs_result = json_decode($bpjs_unCompressedResult, TRUE);

                                        if($bpjs_result['rujukan']['peserta']['mr']['noMR'] === $dataField['MedicalNo'] || $bpjs_result['rujukan']['peserta']['nik'] === $dataField['SSN'])
                                        {
                                            $this->isInMedin = true;
                                            $this->patientData = $dataField;
                                            $this->bpjsData = $bpjs_result['rujukan'];
                                        } else {
                                            return back()->with('error', 'No Rujukan Tidak Cocok Dengan No Rekam Medis / NIK Pasien.');
                                        }
                                    } else {
                                        return back()->with('error', 'No Rujukan Tidak Ditemukan atau Salah.');
                                    }
                                } else {
                                    return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
                                }
                            } catch (RequestException $e) {
                                return back()->with('error', 'An error occurred: ' . $e->getMessage());
                            }
                        } while (!$bpjs_decryptResult);
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
