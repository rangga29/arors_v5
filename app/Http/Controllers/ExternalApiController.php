<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\UmumAppointmentRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExternalApiController extends Controller
{
    /**
     * API 1 - Check Appointment
     * Menerima ap_no dan mengembalikan data pasien umum beserta detail jadwal
     *
     * POST /api/external/appointment/check
     * Body: { "ap_no": "OPA/20260227/00001" }
     */
    public function checkAppointment(Request $request): JsonResponse
    {
        $request->validate([
            'ap_no' => 'required|string',
        ]);

        try {
            $appointment = Appointment::where('ap_no', $request->ap_no)
                ->with([
                    'umumAppointment',
                    'scheduleDetail.schedule.scheduleDate',
                ])
                ->first();

            if (!$appointment) {
                return response()->json([
                    'status' => 'NOT_FOUND',
                    'message' => 'Appointment dengan nomor ' . $request->ap_no . ' tidak ditemukan.',
                ], 404);
            }

            $umumAppointment = $appointment->umumAppointment;

            if (!$umumAppointment) {
                return response()->json([
                    'status' => 'NOT_UMUM',
                    'message' => 'Appointment ini bukan pasien umum.',
                ], 400);
            }

            // Check if already registered
            $existingRegistration = UmumAppointmentRegistration::where('uap_id', $umumAppointment->id)->first();

            $scheduleDetail = $appointment->scheduleDetail;
            $schedule = $scheduleDetail?->schedule;
            $scheduleDate = $schedule?->scheduleDate;

            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Data appointment ditemukan.',
                'data' => [
                    // Appointment
                    'ap_id' => $appointment->id,
                    'ap_ucode' => $appointment->ap_ucode,
                    'ap_no' => $appointment->ap_no,
                    'ap_token' => $appointment->ap_token,
                    'ap_queue' => $appointment->ap_queue,
                    'ap_type' => $appointment->ap_type,
                    'ap_registration_time' => $appointment->ap_registration_time,
                    'ap_appointment_time' => $appointment->ap_appointment_time,

                    // Pasien Umum
                    'uap_id' => $umumAppointment->id,
                    'uap_norm' => $umumAppointment->uap_norm,
                    'uap_name' => $umumAppointment->uap_name,
                    'uap_birthday' => $umumAppointment->uap_birthday,
                    'uap_gender' => $umumAppointment->uap_gender,
                    'uap_phone' => $umumAppointment->uap_phone,

                    // Jadwal
                    'schedule_date' => $scheduleDate?->sd_date,
                    'schedule_day' => $scheduleDate?->sd_day,
                    'clinic_code' => $schedule?->sc_clinic_code,
                    'clinic_name' => $schedule?->sc_clinic_name,
                    'doctor_code' => $schedule?->sc_doctor_code,
                    'doctor_name' => $schedule?->sc_doctor_name,
                    'session' => $scheduleDetail?->scd_session,
                    'start_time' => $scheduleDetail?->scd_start_time,
                    'end_time' => $scheduleDetail?->scd_end_time,

                    // Status registrasi
                    'is_registered' => $existingRegistration !== null,
                    'registration' => $existingRegistration ? [
                        'uar_ucode' => $existingRegistration->uar_ucode,
                        'uar_no' => $existingRegistration->uar_no,
                        'uar_date' => $existingRegistration->uar_date,
                        'uar_session' => $existingRegistration->uar_session,
                        'uar_time' => $existingRegistration->uar_time,
                        'uar_reg_no' => $existingRegistration->uar_reg_no,
                        'uar_reg_status' => $existingRegistration->uar_reg_status,
                        'uar_queue' => $existingRegistration->uar_queue,
                        'uar_room' => $existingRegistration->uar_room,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * API 2 - Register Appointment
     * Menerima ap_no, melakukan registrasi pasien
     * (Sementara mengembalikan response berhasil, nanti akan dihubungkan ke API RS)
     *
     * POST /api/external/appointment/register
     * Body: { "ap_no": "OPA/20260227/00001" }
     */
    public function registerAppointment(Request $request): JsonResponse
    {
        $request->validate([
            'ap_no' => 'required|string',
        ]);

        try {
            $appointment = Appointment::where('ap_no', $request->ap_no)
                ->with([
                    'umumAppointment',
                    'scheduleDetail.schedule.scheduleDate',
                ])
                ->first();

            if (!$appointment) {
                return response()->json([
                    'status' => 'NOT_FOUND',
                    'message' => 'Appointment dengan nomor ' . $request->ap_no . ' tidak ditemukan.',
                ], 404);
            }

            $umumAppointment = $appointment->umumAppointment;

            if (!$umumAppointment) {
                return response()->json([
                    'status' => 'NOT_UMUM',
                    'message' => 'Appointment ini bukan pasien umum.',
                ], 400);
            }

            // Check if already registered
            $existingRegistration = UmumAppointmentRegistration::where('uap_id', $umumAppointment->id)->first();
            if ($existingRegistration) {
                return response()->json([
                    'status' => 'ALREADY_REGISTERED',
                    'message' => 'Pasien sudah terdaftar sebelumnya.',
                    'data' => [
                        'uar_ucode' => $existingRegistration->uar_ucode,
                        'uar_no' => $existingRegistration->uar_no,
                        'uar_date' => $existingRegistration->uar_date,
                        'uar_session' => $existingRegistration->uar_session,
                        'uar_time' => $existingRegistration->uar_time,
                        'uar_reg_no' => $existingRegistration->uar_reg_no,
                        'uar_reg_status' => $existingRegistration->uar_reg_status,
                        'uar_queue' => $existingRegistration->uar_queue,
                        'uar_room' => $existingRegistration->uar_room,
                    ],
                ], 200);
            }

            $scheduleDetail = $appointment->scheduleDetail;
            $schedule = $scheduleDetail?->schedule;
            $scheduleDate = $schedule?->scheduleDate;

            // ======================================================
            // TODO: Ganti dengan API RS yang sebenarnya
            // Nanti di sini akan dipanggil API RS untuk registrasi
            // seperti pada method sendUmumAppointmentToApi di ApiController
            // ======================================================

            // Sementara: Placeholder response berhasil
            return response()->json([
                'status' => 'SUCCESS',
                'message' => 'Registrasi berhasil dilakukan.',
                'data' => [
                    // Appointment
                    'ap_no' => $appointment->ap_no,
                    'ap_queue' => $appointment->ap_queue,

                    // Pasien
                    'uap_norm' => $umumAppointment->uap_norm,
                    'uap_name' => $umumAppointment->uap_name,

                    // Jadwal
                    'schedule_date' => $scheduleDate?->sd_date,
                    'clinic_name' => $schedule?->sc_clinic_name,
                    'doctor_name' => $schedule?->sc_doctor_name,
                    'session' => $scheduleDetail?->scd_session,
                    'start_time' => $scheduleDetail?->scd_start_time,

                    // Registrasi (placeholder, nanti diganti dari response API RS)
                    'registration' => [
                        'registration_id' => 'PLACEHOLDER_REG_ID',
                        'registration_no' => 'PLACEHOLDER_REG_NO',
                        'registration_date' => now()->format('Y-m-d'),
                        'registration_time' => now()->format('H:i:s'),
                        'queue_no' => $appointment->ap_queue,
                        'room' => '-',
                        'status' => 'REGISTERED',
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }
}
