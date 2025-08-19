<?php

namespace App\Livewire\SundayClinic;

use App\Services\APIBpjsHeaderGenerator;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentOpen;
use App\Services\SCAppointmentDate;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\View;
use Livewire\Component;
use LZCompressor\LZString;
use function back;
use function base64_decode;
use function date_default_timezone_set;
use function env;
use function hash;
use function hex2bin;
use function json_decode;
use function now;
use function openssl_decrypt;
use function pow;
use function substr;
use const OPENSSL_RAW_DATA;

class SCNewPatientCheck extends Component
{
    public $nik, $birthday;
    public $isInBpjs, $patientData;
    protected APIHeaderGenerator $apiHeaderGenerator;
    protected APIBpjsHeaderGenerator $apiBpjsHeaderGenerator;
    protected AppointmentOpen $appointmentOpen;
    protected SCAppointmentDate $scAppointmentDate;

    public function boot(APIHeaderGenerator $apiHeaderGenerator, APIBpjsHeaderGenerator $apiBpjsHeaderGenerator, AppointmentOpen $appointmentOpen, SCAppointmentDate $scAppointmentDate): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
        $this->apiBpjsHeaderGenerator = $apiBpjsHeaderGenerator;
        $this->appointmentOpen = $appointmentOpen;
        $this->scAppointmentDate = $scAppointmentDate;
    }

    public function render()
    {
        View::share('type', 'sunday-clinic');
        return view('livewire.new-sunday-clinic.nsc-patient-check', [
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
        $headers = $this->apiHeaderGenerator->generateApiHeader();
        $headerBpjs = $this->apiBpjsHeaderGenerator->generateApiBpjsHeader();

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Ymd');
            $birthdateBpjs = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Y-m-d');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah');
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
                                return back()->with('error', 'Terjadi Kesalahan. Silahkan Dicoba Kembali.');
                            }
                        } else {
                            break;
                        }
                    }

                    $bpjs_unCompressedResult = LZString::decompressFromEncodedURIComponent($bpjs_decryptResult);
                    $bpjs_result = json_decode($bpjs_unCompressedResult, TRUE);

                    if($bpjs_result['peserta']['mr']['noMR']) {
                        return back()->with('error', 'NIK Sudah Terdaftar di Sistem Kami dengan NORM ' . $bpjs_result['peserta']['mr']['noMR'] . '. Silahkan Registrasi Sebagai Pasien Umum / Kontraktor.');
                    } else if($bpjs_result['peserta']['tglLahir'] != $birthdateBpjs) {
                        return back()->with('error', 'Tanggal Lahir Tidak Cocok.');
                    } else {
                        $this->isInBpjs = true;
                        $this->patientData = $bpjs_result['peserta'];
                    }
                } else {
                    return back()->with('error', 'Data Salah Atau Tidak Ditemukan.');
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $responseBpjs->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
}
