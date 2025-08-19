<?php

namespace App\Livewire\Batal;

use App\Models\Appointment;
use App\Models\FisioterapiAppointment;
use App\Services\APIHeaderGenerator;
use App\Services\AppointmentDate;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class ShowBatalNorm extends Component
{
    public $umumData, $asuransiData, $fisioterapiData, $bpjsData;

    protected APIHeaderGenerator $apiHeaderGenerator;

    public function boot(APIHeaderGenerator $apiHeaderGenerator): void
    {
        date_default_timezone_set('Asia/Jakarta');
        $this->apiHeaderGenerator = $apiHeaderGenerator;
    }

    public function render()
    {
        View::share('type', 'batal');
        return view('livewire.batal.show-batal-norm')
            ->layout('frontend.layout', [
                'subTitle' => 'Pembatalan Nomor Antrian Norm',
                'description' => 'Pembatalan Nomor Antrian Norm Registrasi Online Rumah Sakit Cahya Kawaluyan',
                'subKeywords' => 'pembatalan nomor antrian, pembatalan antrian, pembatalan antrian norm, pembatalan nomor antrian norm'
            ]);
    }

    public function mount($umumData, $asuransiData, $bpjsData, $fisioterapiData): void
    {
        $this->umumData = $umumData;
        $this->asuransiData = $asuransiData;
        $this->bpjsData = $bpjsData;
        $this->fisioterapiData = $fisioterapiData;
    }

    public function deletePatient(string $ap_no, string $type)
    {
        $link = env('API_KEY', 'rsck');

        $requestData = [
            'AppointmentNo' => $ap_no,
            'CancelReason' => 'Pembatalan Dari ARORS'
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
            $response = $client->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/appointment/base/cancel1", [
                'headers' => $headers,
                'form_params' => $requestData
            ]);
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody(), true);
                if($data['Status'] == 'SUCCESS (000)') {
                    $dataField = json_decode($data['Data'], true);

                    DB::beginTransaction();
                    try {
                        $appointment = Appointment::with('scheduleDetail')->where('ap_no', $ap_no)->first();

                        if ($appointment) {
                            $scheduleDetail = $appointment->scheduleDetail;

                            if ($type === 'umum') {
                                if ($scheduleDetail && $scheduleDetail->scd_counter_online_umum > 0) {
                                    $scheduleDetail->decrement('scd_counter_online_umum');
                                }
                            } elseif ($type === 'asuransi') {
                                if ($scheduleDetail && $scheduleDetail->scd_counter_online_umum > 0) {
                                    $scheduleDetail->decrement('scd_counter_online_umum');
                                }
                            } elseif ($type === 'bpjs') {
                                if ($scheduleDetail && $scheduleDetail->scd_counter_online_bpjs > 0) {
                                    $scheduleDetail->decrement('scd_counter_online_bpjs');
                                }
                            }
                            $appointment->delete();
                        }

                        DB::commit();
                        return redirect()->route('batal-antrian.norm')->with('success', 'Nomor Antrian Berhasil Dibatalkan');

                    } catch (\Exception $e) {
                        DB::rollBack();
                        return redirect()->route('batal-antrian.norm')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih.');
                    }
                } else {
                    return redirect()->route('batal-antrian.norm')->with('error', $data['Status'] . ' - ' . $data['Remarks']);
                }
            } else {
                return redirect()->route('batal-antrian.norm')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [' . $response->getStatusCode() . ']');
            }
        } catch (RequestException $e) {
            return redirect()->route('batal-antrian.norm')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih. [500]');
        }
    }

    public function deletePatientFisio(string $fap_ucode)
    {
        DB::beginTransaction();
        try {
            $appointmentRecord = FisioterapiAppointment::where('fap_ucode', $fap_ucode)->first();
            if ($appointmentRecord) {
                $appointmentRecord->delete();
            } else {
                DB::rollBack();
                return redirect()->route('batal-antrian.norm')->with('error', 'Nomor Antrian Tidak Ditemukan');
            }
            DB::commit();
            return redirect()->route('batal-antrian.norm')->with('success', 'Nomor Antrian Berhasil Dibatalkan');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('batal-antrian.norm')->with('error', 'Mohon Maaf Terjadi Kesalahan Pada Sistem. Silahkan Menghubungi Customer Service di 0812 1111 8009. Terima Kasih.');
        }
    }
}
