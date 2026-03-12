<?php

namespace App\Livewire\Batal;

use App\Models\Appointment;
use App\Models\FisioterapiAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Services\APIHeaderGenerator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class ShowBatalNorm extends Component
{
    public $appointmentList;

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

    public function mount($appointmentList): void
    {
        $enriched = [];
        foreach ($appointmentList as $appointment) {
            // Determine session based on clinic code + date + time range
            $appointment['_session'] = $this->findSession($appointment);

            // Calculate registration time (30 min before appointment start)
            $startTime = $appointment['AppointmentStartTime'] ?? null;
            if ($startTime) {
                try {
                    $regTime = Carbon::createFromFormat('H:i', $startTime)
                        ->subMinutes(30)
                        ->format('H:i');
                    $appointment['_registration_time'] = $regTime;
                } catch (\Exception $e) {
                    $appointment['_registration_time'] = $startTime;
                }
            } else {
                $appointment['_registration_time'] = '-';
            }

            $enriched[] = $appointment;
        }

        $this->appointmentList = $enriched;
    }

    /**
     * Find session by matching ServiceUnitCode + date to Schedule,
     * then checking which ScheduleDetail time range the appointment falls into.
     */
    private function findSession(array $appointment): ?int
    {
        $clinicCode = $appointment['ServiceUnitCode'] ?? null;
        $startDate = $appointment['StartDate'] ?? null;
        $appointmentTime = $appointment['AppointmentStartTime'] ?? null;
        $departmentID = $appointment['DepartmentID'] ?? 'OUTPATIENT';
        $serviceUnitName = strtoupper($appointment['ServiceUnitName'] ?? '');

        if (!$startDate || !$appointmentTime) {
            return null;
        }

        // Fisioterapi: pagi = sesi 1, sore = sesi 2
        // Boundary: 14:00 weekday, 12:00 Saturday
        $isFisio = $departmentID === 'DIAGNOSTIC' || str_contains($serviceUnitName, 'FISIOTERAPI');
        if ($isFisio) {
            $isSaturday = Carbon::createFromFormat('Y-m-d', $startDate)->isSaturday();
            $boundary = $isSaturday ? '12:00' : '14:00';
            return $appointmentTime < $boundary ? 1 : 2;
        }

        if (!$clinicCode) {
            return null;
        }

        // Find ScheduleDate for the given date
        $scheduleDate = ScheduleDate::where('sd_date', $startDate)->first();
        if (!$scheduleDate) {
            return null;
        }

        // Find Schedule for the clinic on that date
        $schedule = Schedule::where('sd_id', $scheduleDate->id)
            ->where('sc_clinic_code', $clinicCode)
            ->first();

        if (!$schedule) {
            return null;
        }

        // Find which ScheduleDetail's time range contains the appointment time
        $scheduleDetails = ScheduleDetail::where('sc_id', $schedule->id)
            ->orderBy('scd_session')
            ->get();

        foreach ($scheduleDetails as $detail) {
            $scdStart = $detail->scd_start_time;
            $scdEnd = $detail->scd_end_time;

            if ($appointmentTime >= substr($scdStart, 0, 5) && $appointmentTime <= substr($scdEnd, 0, 5)) {
                return $detail->scd_session;
            }
        }

        // If not found in range, try to find the closest session
        if ($scheduleDetails->isNotEmpty()) {
            $firstDetail = $scheduleDetails->first();
            if ($appointmentTime <= substr($firstDetail->scd_end_time, 0, 5)) {
                return $firstDetail->scd_session;
            }
            return $scheduleDetails->last()->scd_session;
        }

        return null;
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
                if ($data['Status'] == 'SUCCESS (000)') {
                    $dataField = json_decode($data['Data'], true);

                    DB::beginTransaction();
                    try {
                        $appointment = Appointment::with('scheduleDetail')->where('ap_no', $ap_no)->first();

                        if ($appointment) {
                            $scheduleDetail = $appointment->scheduleDetail;

                            if ($type === 'bpjs') {
                                if ($scheduleDetail && $scheduleDetail->scd_counter_online_bpjs > 0) {
                                    $scheduleDetail->decrement('scd_counter_online_bpjs');
                                }
                            } else {
                                // umum / asuransi mengurangi kuota umum
                                if ($scheduleDetail && $scheduleDetail->scd_counter_online_umum > 0) {
                                    $scheduleDetail->decrement('scd_counter_online_umum');
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
