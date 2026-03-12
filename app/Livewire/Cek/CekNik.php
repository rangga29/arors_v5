<?php

namespace App\Livewire\Cek;

use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class CekNik extends Component
{
    public $nik, $birthday, $selectedDate, $appointmentDates;
    public $isHaveAppointment = false;
    public $appointmentList = [];

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
        View::share('type', 'cek');
        return view('livewire.cek.cek-nik', [
            'todayDate' => Carbon::today()->format('Y-m-d'),
            'appointmentDate' => $this->appointmentDate->selectAppointmentDate(),
        ])->layout('frontend.layout', [
            'subTitle' => 'Cek Nomor Antrian NIK',
            'description' => 'Cek Nomor Antrian NIK Registrasi Online Rumah Sakit Cahya Kawaluyan',
            'subKeywords' => 'cek nomor antrian, cek antrian, cek antrian nik, cek nomor antrian nik'
        ]);
    }

    public function mount(): void
    {
        $this->appointmentDates = $this->appointmentDate->selectNextSevenAppointmentDates();
    }

    public function checkPatient()
    {
        $link = env('API_KEY', 'rsck');
        $date = $this->selectedDate;
        $headers = $this->apiHeaderGenerator->generateApiHeader();

        try {
            $birthdate = Carbon::createFromFormat('d/m/Y', $this->birthday)->format('Ymd');
        } catch (InvalidFormatException) {
            return back()->with('error', 'Format Tanggal Lahir Salah');
        }

        $dateFormatted = Carbon::createFromFormat('Y-m-d', $date)->format('Ymd');

        $handlerStack = HandlerStack::create();
        $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
            return $retry < 10 && $exception instanceof RequestException && $exception->getCode() === 28;
        }, function ($retry) {
            return 1000 * pow(2, $retry);
        }));

        try {
            $client = new Client(['handler' => $handlerStack, 'verify' => false]);
            $response = $client->get("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/appointment/base/list/information/{$dateFormatted}/{$dateFormatted}?SSN={$this->nik}", [
                'headers' => $headers,
            ]);

            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);

                if (isset($data['Data'])) {
                    $dataField = json_decode($data['Data'], true);

                    if (!empty($dataField['AppointmentHistory'])) {
                        // Validasi tanggal lahir dari data pertama
                        $apiDob = $dataField['AppointmentHistory'][0]['DateOfBirth'] ?? '';
                        $apiDobFormatted = Carbon::createFromFormat('d-M-Y', $apiDob)->format('Ymd');

                        if ($birthdate != $apiDobFormatted) {
                            return back()->with('error', 'Data Pasien Tidak Sesuai. Silahkan Cek Kembali Tanggal Lahir Pasien.');
                        }
                        $this->isHaveAppointment = true;
                        $appointments = $dataField['AppointmentHistory'];
                        usort($appointments, function ($a, $b) {
                            return strcmp($a['AppointmentNo'], $b['AppointmentNo']);
                        });
                        $this->appointmentList = $appointments;
                    } else {
                        return back()->with('error', 'Data Antrian Tidak Ditemukan. Silahkan Cek Kembali Data Pasien.');
                    }
                } else {
                    return back()->with('error', 'Data Pasien Tidak Ditemukan. Silahkan Cek Kembali NIK Pasien.');
                }
            } else {
                return back()->with('error', 'Request failed. Status code: ' . $response->getStatusCode());
            }
        } catch (RequestException $e) {
            return back()->with('error', 'Terjadi kesalahan koneksi. Silahkan coba lagi.');
        }
    }
}
