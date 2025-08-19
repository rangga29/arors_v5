<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use App\Models\UmumAppointment;
use App\Models\UmumAppointmentRegistration;
use App\Services\APIHeaderGenerator;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;

class ApiController extends Controller
{
    protected APIHeaderGenerator $apiHeaderGenerator;

    public function __construct(APIHeaderGenerator $apiHeaderGenerator)
    {
        $this->apiHeaderGenerator = $apiHeaderGenerator;
    }

    public function getAppointmentByToken($date, $token)
    {
        try {
            $appointment = ScheduleDate::where('sd_date', $date)
                ->with([
                    'schedules' => function ($query) use ($token) {
                        $query->whereHas('scheduleDetails.appointments', function ($subQuery) use ($token) {
                            $subQuery->where('ap_token', $token);
                        })->with([
                            'scheduleDetails' => function ($subQuery) use ($token) {
                                $subQuery->whereHas('appointments', function ($subSubQuery) use ($token) {
                                    $subSubQuery->where('ap_token', $token);
                                })->with([
                                    'appointments' => function ($subSubQuery) use ($token) {
                                        $subSubQuery->where('ap_token', $token);
                                    },
                                    'appointments.umumAppointment',
                                    'appointments.asuransiAppointment',
                                    'appointments.bpjsKesehatanAppointment',
                                    'appointments.newAppointment',
                                ]);
                            },
                        ]);
                    },
                ])
                ->first()?->toArray();

            if (!$appointment['schedules']) {
                return response()->json([
                    'type' => 'TIDAK ADA',
                    'message' => 'KODE TOKEN TIDAK DITEMUKAN'
                ], 404);
            }

            $umumAppointment = data_get($appointment, 'schedules.0.schedule_details.0.appointments.0.umum_appointment');
            $asuransiAppointment = data_get($appointment, 'schedules.0.schedule_details.0.appointments.0.asuransi_appointment');
            $bpjsKesehatanAppointment = data_get($appointment, 'schedules.0.schedule_details.0.appointments.0.bpjs_kesehatan_appointment');
            $newAppointment = data_get($appointment, 'schedules.0.schedule_details.0.appointments.0.new_appointment');

            $uar_data = null;
            if ($umumAppointment !== null) {
                if (UmumAppointmentRegistration::where('uap_id', $umumAppointment['id'])->doesntExist()) {
                    $app_data = Appointment::where('ap_ucode', data_get($appointment, 'schedules.0.schedule_details.0.appointments.0.ap_ucode'))->first();
                    $umum_data = UmumAppointment::where('ap_id', $app_data['id'])->first();

                    $responses = [];
                    $link = env('API_KEY', 'rsck');
                    $headers = $this->apiHeaderGenerator->generateApiHeader();

                    $requestData = [
                        'AppointmentNo' => $app_data['ap_no'],
                        'MedicalNo' => $umum_data['uap_norm']
                    ];

                    $handlerStack = HandlerStack::create();
                    $handlerStack->push(Middleware::retry(function ($retry, $request, $response, $exception) {
                        return $retry < 3 && $exception instanceof RequestException && $exception->getCode() === 28;
                    }, function ($retry) {
                        return 1000 * pow(2, $retry);
                    }));

                    $client = new Client(['handler' => $handlerStack, 'verify' => false]);
                    $response = $client->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/appointment/insert/apm/registration1", [
                        'headers' => $headers,
                        'form_params' => $requestData
                    ]);

                    if ($response->getStatusCode() == 200) {
                        $data = json_decode($response->getBody(), true);
                        if($data['Status'] == 'SUCCESS') {
                            $dataField = json_decode($data['Data'], true);
                            $uar_data = UmumAppointmentRegistration::create([
                                'uap_id' => $umum_data['id'],
                                'uar_ucode' => $dataField['RegistrationID'],
                                'uar_no' => $dataField['RegistrationNo'],
                                'uar_date' => Carbon::createFromFormat('d-M-Y', $dataField['RegistrationDate'])->format('Y-m-d'),
                                'uar_session' => $dataField['Session'],
                                'uar_time' => Carbon::createFromFormat('H:i', $dataField['RegistrationTime'])->format('H:i:s'),
                                'uar_reg_no' => $dataField['RegistrationTicketNo'],
                                'uar_reg_status' => $dataField['RegistrationStatus'],
                                'uar_queue' => $dataField['QueueNo'],
                                'uar_room' => $dataField['Room'],
                            ]);
                        } else {
                            $message = $data['Status'] . ' - ' . $data['Remarks'];
                        }
                    } else {
                        $message = 'Request failed. Status code: ' . $response->getStatusCode();
                    }

                    return response()->json([
                        'type' => 'UMUM',
                        'message' => $message ?? 'OPR BERHASIL DIBUAT',
                        'appointment' => $appointment,
                        'uar' => $uar_data
                    ]);
                } else {
                    $uar_data = UmumAppointmentRegistration::where('uap_id', $umumAppointment['id'])->first();
                    return response()->json([
                        'type' => 'UMUM',
                        'message' => 'OPR SUDAH PERNAH DIBUAT SEBELUMNYA',
                        'appointment' => $appointment,
                        'uar' => $uar_data
                    ]);
                }
            } elseif ($asuransiAppointment !== null || $bpjsKesehatanAppointment !== null || $newAppointment !== null) {
                return response()->json([
                    'type' => 'NON-UMUM',
                    'message' => 'MOHON MAAF ANDA TIDAK TERDAFTAR SEBAGAI PASIEN UMUM'
                ], 400);
            }
        } catch (\Exception $e) {
            return response()->json([
                'type' => 'ERROR',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAppointmentByOpa($date, $opaCode)
    {
        try {
            $opa = 'OPA/' . Carbon::now()->format('Ymd') . '/' . $opaCode;

            // Fetch the appointment data with relationships
            $appointment = ScheduleDate::where('sd_date', $date)
                ->with([
                    'schedules.scheduleDetails.appointments.umumAppointment',
                    'schedules.scheduleDetails.appointments.asuransiAppointment',
                    'schedules.scheduleDetails.appointments.bpjsKesehatanAppointment',
                    'schedules.scheduleDetails.appointments.newAppointment',
                ])
                ->whereHas('schedules.scheduleDetails.appointments', function ($query) use ($opa) {
                    $query->where('ap_no', $opa);
                })
                ->first();

            // Handle if no appointment found
            if (!$appointment || $appointment->schedules->isEmpty()) {
                return response()->json([
                    'type' => 'TIDAK ADA',
                    'message' => 'SCHEDULE ATAU TOKEN TIDAK DITEMUKAN',
                ], 404);
            }

            // Flatten and filter appointments
            $appointments = $appointment->schedules
                ->flatMap(fn($schedule) => $schedule->scheduleDetails)
                ->flatMap(fn($detail) => $detail->appointments)
                ->filter(fn($appt) => $appt->ap_no === $opa);

            if ($appointments->isEmpty()) {
                return response()->json([
                    'type' => 'TIDAK ADA',
                    'message' => 'KODE TOKEN TIDAK DITEMUKAN',
                ], 404);
            }

            $firstAppointment = $appointments->first();

            // Handle each type of appointment
            $appointmentType = [
                'umum' => $firstAppointment->umumAppointment ?? null,
                'asuransi' => $firstAppointment->asuransiAppointment ?? null,
                'bpjs' => $firstAppointment->bpjsKesehatanAppointment ?? null,
                'new' => $firstAppointment->newAppointment ?? null,
            ];

            if ($appointmentType['umum'] !== null) {
                $umumAppointment = $appointmentType['umum'];

                if (UmumAppointmentRegistration::where('uap_id', $umumAppointment['id'])->doesntExist()) {
                    return $this->sendUmumAppointmentToApi($umumAppointment);
                } else {
                    // Return if already registered
                    $uarData = UmumAppointmentRegistration::where('uap_id', $umumAppointment['id'])->first();
                    $uapData = UmumAppointment::where('id', $uarData['uap_id'])->first();
                    $apData = Appointment::where('id', $uapData['ap_id'])->first();
                    $scdData = ScheduleDetail::where('id', $apData['scd_id'])->first();
                    $scData = Schedule::where('id', $scdData['sc_id'])->first();
                    $sdData = ScheduleDate::where('id', $scData['sd_id'])->first();

                    return response()->json([
                        'type' => 'UMUM',
                        'message' => 'OPR SUDAH PERNAH DIBUAT SEBELUMNYA',
                        'scheduleDate' => $sdData,
                        'schedule' => $scData,
                        'scheduleDetail' => $scdData,
                        'appointment' => $apData,
                        'umumAppointment' => $uapData,
                        'umumRegistration' => $uarData,
                    ]);
                }
            }

            // Handle non-UMUM cases
            if ($appointmentType['asuransi'] !== null || $appointmentType['bpjs'] !== null || $appointmentType['new'] !== null) {
                return response()->json([
                    'type' => 'NON-UMUM',
                    'message' => 'MOHON MAAF ANDA TIDAK TERDAFTAR SEBAGAI PASIEN UMUM',
                ], 400);
            }
        } catch (\Exception $e) {
            // Handle errors
            return response()->json([
                'type' => 'ERROR',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    private function sendUmumAppointmentToApi($umumAppointment)
    {
        $requestData = [
            'AppointmentNo' => Appointment::where('id', $umumAppointment['ap_id'])->first()['ap_no'],
            'MedicalNo' => $umumAppointment['uap_norm'],
        ];

        $client = new Client([
            'handler' => HandlerStack::create(),
            'verify' => false,
        ]);

        $link = env('API_KEY', 'rsck');
        $response = $client->post("https://mobilejkn.rscahyakawaluyan.com/medinfrasAPI/{$link}/api/appointment/insert/apm/registration1", [
            'headers' => $this->apiHeaderGenerator->generateApiHeader(),
            'form_params' => $requestData,
        ]);

        if ($response->getStatusCode() === 200) {
            $data = json_decode($response->getBody(), true);
            if ($data['Status'] === 'SUCCESS') {
                $dataField = json_decode($data['Data'], true);

                $uarData = UmumAppointmentRegistration::create([
                    'uap_id' => $umumAppointment['id'],
                    'uar_ucode' => $dataField['RegistrationID'],
                    'uar_no' => $dataField['RegistrationNo'],
                    'uar_date' => Carbon::createFromFormat('d-M-Y', $dataField['RegistrationDate'])->format('Y-m-d'),
                    'uar_session' => $dataField['Session'],
                    'uar_time' => Carbon::createFromFormat('H:i', $dataField['RegistrationTime'])->format('H:i:s'),
                    'uar_reg_no' => $dataField['RegistrationTicketNo'],
                    'uar_reg_status' => $dataField['RegistrationStatus'],
                    'uar_queue' => $dataField['QueueNo'],
                    'uar_room' => $dataField['Room'],
                ]);

                $uapData = UmumAppointment::where('id', $uarData['uap_id'])->first();
                $apData = Appointment::where('id', $uapData['ap_id'])->first();
                $scdData = ScheduleDetail::where('id', $apData['scd_id'])->first();
                $scData = Schedule::where('id', $scdData['sc_id'])->first();
                $sdData = ScheduleDate::where('id', $scData['sd_id'])->first();

                return response()->json([
                    'type' => 'UMUM',
                    'message' => 'OPR BERHASIL DIBUAT',
                    'scheduleDate' => $sdData,
                    'schedule' => $scData,
                    'scheduleDetail' => $scdData,
                    'appointment' => $apData,
                    'umumAppointment' => $uapData,
                    'umumRegistration' => $uarData,
                ]);
            }

            return response()->json([
                'type' => 'ERROR',
                'message' => $data['Status'] . ' - ' . $data['Remarks'],
            ], 400);
        }

        return response()->json([
            'type' => 'ERROR',
            'message' => 'Request failed. Status code: ' . $response->getStatusCode(),
        ], 500);
    }
}
