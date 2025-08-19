<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\FisioterapiAppointment;
use App\Models\Schedule;
use App\Models\ScheduleDate;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function index($date)
    {
        $this->authorize('view', Appointment::class);

        $date_original = $date;
        $date = Carbon::parse($date)->isoFormat('dddd, DD MMMM YYYY');
        $schedule_date_first = ScheduleDate::orderBy('sd_date', 'ASC')->first()->sd_date;
        $schedule_date_last = ScheduleDate::orderBy('sd_date', 'DESC')->first()->sd_date;

        $todayScheduleDates = ScheduleDate::where('sd_date', $date_original)
            ->with('schedules.scheduleDetails.appointments.umumAppointment', 'schedules.scheduleDetails.appointments.asuransiAppointment', 'schedules.scheduleDetails.appointments.bpjsKesehatanAppointment', 'schedules.scheduleDetails.appointments.newAppointment')
            ->get();

        $combinedAppointments = [];
        foreach ($todayScheduleDates as $todayScheduleDate) {
            foreach ($todayScheduleDate->schedules as $todaySchedule) {
                foreach($todaySchedule->scheduleDetails as $todayScheduleDetail) {
                    foreach ($todayScheduleDetail->appointments as $todayAppointment) {
                        $combinedAppointment = [
                            'clinic' => $todaySchedule->sc_clinic_name,
                            'doctor' => $todaySchedule->sc_doctor_name,
                            'session' => $todayScheduleDetail->scd_session,
                            'queue' => $todayAppointment->ap_queue,
                            'token' => $todayAppointment->ap_token,
                            'norm' => null,
                            'name' => null,
                            'birthday' => null,
                            'phone' => null,
                            'type' => $todayAppointment->ap_type,
                            'registration_time' => $todayAppointment->ap_registration_time,
                            'appointment_time' => $todayAppointment->ap_appointment_time
                        ];

                        if ($todayAppointment->umumAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->umumAppointment->uap_norm,
                                'name' => $todayAppointment->umumAppointment->uap_name,
                                'birthday' => $todayAppointment->umumAppointment->uap_birthday,
                                'phone' => $todayAppointment->umumAppointment->uap_phone,
                            ]);
                        }

                        if ($todayAppointment->asuransiAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->asuransiAppointment->aap_norm,
                                'name' => $todayAppointment->asuransiAppointment->aap_name,
                                'birthday' => $todayAppointment->asuransiAppointment->aap_birthday,
                                'phone' => $todayAppointment->asuransiAppointment->aap_phone,
                            ]);
                        }

                        if ($todayAppointment->bpjsKesehatanAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->bpjsKesehatanAppointment->bap_norm,
                                'name' => $todayAppointment->bpjsKesehatanAppointment->bap_name,
                                'birthday' => $todayAppointment->bpjsKesehatanAppointment->bap_birthday,
                                'phone' => $todayAppointment->bpjsKesehatanAppointment->bap_phone,
                            ]);
                        }

                        if ($todayAppointment->newAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->newAppointment->nap_norm,
                                'name' => $todayAppointment->newAppointment->nap_name,
                                'birthday' => $todayAppointment->newAppointment->nap_birthday,
                                'phone' => $todayAppointment->newAppointment->nap_phone,
                            ]);
                        }

                        $combinedAppointments[] = $combinedAppointment;
                    }
                }
            }
        }

        usort($combinedAppointments, function ($a, $b) {
            $clinicComparison = strcmp($a['clinic'], $b['clinic']);
            if ($clinicComparison !== 0) {
                return $clinicComparison;
            }

            $doctorComparison = strcmp($a['doctor'], $b['doctor']);
            if ($doctorComparison !== 0) {
                return $doctorComparison;
            }

            $sessionComparison = strcmp($a['session'], $b['session']);
            if ($sessionComparison !== 0) {
                return $sessionComparison;
            }

            return $a['queue'] - $b['queue'];
        });

        return view('backend.appointment.view-date', [
            'date_original' => $date_original,
            'date' => $date,
            'schedule_date_first' => $schedule_date_first,
            'schedule_date_last' => $schedule_date_last,
            'appointmentData' => $combinedAppointments
        ]);
    }

    public function redirectDate(Request $request)
    {
        $this->authorize('view', Appointment::class);

        return redirect()->route('appointments', $request['appointment-date']);
    }

    public function indexDoctor($date, $clinic, $doctor, $session)
    {
        $this->authorize('view', Appointment::class);

        $date_original = $date;
        $date = Carbon::parse($date)->isoFormat('dddd, DD MMMM YYYY');
        $schedule_date_first = ScheduleDate::orderBy('sd_date', 'ASC')->first()->sd_date;
        $schedule_date_last = ScheduleDate::orderBy('sd_date', 'DESC')->first()->sd_date;
        $schedule = Schedule::where('sc_clinic_code', $clinic)->where('sc_doctor_code', $doctor)->first();

        $todayScheduleDates = ScheduleDate::where('sd_date', $date_original)
            ->with('schedules.scheduleDetails.appointments.umumAppointment', 'schedules.scheduleDetails.appointments.asuransiAppointment', 'schedules.scheduleDetails.appointments.bpjsKesehatanAppointment', 'schedules.scheduleDetails.appointments.newAppointment')
            ->get();

        $combinedAppointments = [];
        foreach ($todayScheduleDates as $todayScheduleDate) {
            foreach ($todayScheduleDate->schedules as $todaySchedule) {
                foreach($todaySchedule->scheduleDetails as $todayScheduleDetail) {
                    foreach ($todayScheduleDetail->appointments as $todayAppointment) {
                        if($todaySchedule->sc_clinic_code == $clinic && $todaySchedule->sc_doctor_code == $doctor && $todayScheduleDetail->scd_session == $session) {
                            $combinedAppointment = [
                                'clinic' => $todaySchedule->sc_clinic_name,
                                'doctor' => $todaySchedule->sc_doctor_name,
                                'session' => $todayScheduleDetail->scd_session,
                                'queue' => $todayAppointment->ap_queue,
                                'token' => $todayAppointment->ap_token,
                                'norm' => null,
                                'name' => null,
                                'birthday' => null,
                                'phone' => null,
                                'type' => $todayAppointment->ap_type,
                                'registration_time' => $todayAppointment->ap_registration_time,
                                'appointment_time' => $todayAppointment->ap_appointment_time
                            ];

                            if ($todayAppointment->umumAppointment) {
                                $combinedAppointment = array_merge($combinedAppointment, [
                                    'norm' => $todayAppointment->umumAppointment->uap_norm,
                                    'name' => $todayAppointment->umumAppointment->uap_name,
                                    'birthday' => $todayAppointment->umumAppointment->uap_birthday,
                                    'phone' => $todayAppointment->umumAppointment->uap_phone,
                                ]);
                            }

                            if ($todayAppointment->asuransiAppointment) {
                                $combinedAppointment = array_merge($combinedAppointment, [
                                    'norm' => $todayAppointment->asuransiAppointment->aap_norm,
                                    'name' => $todayAppointment->asuransiAppointment->aap_name,
                                    'birthday' => $todayAppointment->asuransiAppointment->aap_birthday,
                                    'phone' => $todayAppointment->asuransiAppointment->aap_phone,
                                ]);
                            }

                            if ($todayAppointment->bpjsKesehatanAppointment) {
                                $combinedAppointment = array_merge($combinedAppointment, [
                                    'norm' => $todayAppointment->bpjsKesehatanAppointment->bap_norm,
                                    'name' => $todayAppointment->bpjsKesehatanAppointment->bap_name,
                                    'birthday' => $todayAppointment->bpjsKesehatanAppointment->bap_birthday,
                                    'phone' => $todayAppointment->bpjsKesehatanAppointment->bap_phone,
                                ]);
                            }

                            if ($todayAppointment->newAppointment) {
                                $combinedAppointment = array_merge($combinedAppointment, [
                                    'norm' => $todayAppointment->newAppointment->nap_norm,
                                    'name' => $todayAppointment->newAppointment->nap_name,
                                    'birthday' => $todayAppointment->newAppointment->nap_birthday,
                                    'phone' => $todayAppointment->newAppointment->nap_phone,
                                ]);
                            }

                            $combinedAppointments[] = $combinedAppointment;
                        }
                    }
                }
            }
        }

        usort($combinedAppointments, function ($a, $b) {
            $clinicComparison = strcmp($a['clinic'], $b['clinic']);
            if ($clinicComparison !== 0) {
                return $clinicComparison;
            }

            $doctorComparison = strcmp($a['doctor'], $b['doctor']);
            if ($doctorComparison !== 0) {
                return $doctorComparison;
            }

            $sessionComparison = strcmp($a['session'], $b['session']);
            if ($sessionComparison !== 0) {
                return $sessionComparison;
            }

            return $a['queue'] - $b['queue'];
        });

        return view('backend.appointment.view-date-doctor', [
            'date_original' => $date_original,
            'date' => $date,
            'clinic_code' => $clinic,
            'doctor_code' => $doctor,
            'schedule_date_first' => $schedule_date_first,
            'schedule_date_last' => $schedule_date_last,
            'schedule' => $schedule,
            'session' => $session,
            'appointmentData' => $combinedAppointments
        ]);
    }

    public function redirectDateDoctor(Request $request)
    {
        $this->authorize('view', Appointment::class);

        return redirect()->route('appointments.doctor', [$request['appointment-date'], $request['clinic-code'], $request['doctor-code'], $request['doctor-session']]);
    }

    public function indexFisio($date)
    {
        $this->authorize('view', Appointment::class);

        $date_original = $date;
        $date = Carbon::parse($date)->isoFormat('dddd, DD MMMM YYYY');
        $schedule_date_first = ScheduleDate::orderBy('sd_date', 'ASC')->first()->sd_date;
        $schedule_date_last = ScheduleDate::orderBy('sd_date', 'DESC')->first()->sd_date;

        $appointmentsUmumPagi = FisioterapiAppointment::where('sd_id', (ScheduleDate::where('sd_date', $date_original)->first()->id))
            ->where('fap_type', 'UMUM PAGI')
            ->orderBy('fap_queue')->get();

        $appointmentsUmumSore = FisioterapiAppointment::where('sd_id', (ScheduleDate::where('sd_date', $date_original)->first()->id))
            ->where('fap_type', 'UMUM SORE')
            ->orderBy('fap_queue')->get();

        $appointmentsBpjsPagi = FisioterapiAppointment::where('sd_id', (ScheduleDate::where('sd_date', $date_original)->first()->id))
            ->where('fap_type', 'BPJS PAGI')
            ->orderBy('fap_queue')->get();

        $appointmentsBpjsSore = FisioterapiAppointment::where('sd_id', (ScheduleDate::where('sd_date', $date_original)->first()->id))
            ->where('fap_type', 'BPJS SORE')
            ->orderBy('fap_queue')->get();

        return view('backend.appointment.view-fisio', [
            'date_original' => $date_original,
            'date' => $date,
            'schedule_date_first' => $schedule_date_first,
            'schedule_date_last' => $schedule_date_last,
            'appointmentDataUmumPagi' => $appointmentsUmumPagi,
            'appointmentDataUmumSore' => $appointmentsUmumSore,
            'appointmentDataBpjsPagi' => $appointmentsBpjsPagi,
            'appointmentDataBpjsSore' => $appointmentsBpjsSore
        ]);
    }

    public function redirectFisio(Request $request)
    {
        $this->authorize('view', Appointment::class);

        return redirect()->route('appointments.fisioterapi', $request['appointment-date']);
    }

    public function printAppointment($date)
    {
        $fileName = Carbon::createFromFormat('Y-m-d', $date)->format('Ymd') . '_DataAppointment';

        $todayScheduleDates = ScheduleDate::where('sd_date', $date)
            ->with('schedules.scheduleDetails.appointments.umumAppointment', 'schedules.scheduleDetails.appointments.asuransiAppointment', 'schedules.scheduleDetails.appointments.bpjsKesehatanAppointment', 'schedules.scheduleDetails.appointments.newAppointment')
            ->get();

        $combinedAppointments = [];
        foreach ($todayScheduleDates as $todayScheduleDate) {
            foreach ($todayScheduleDate->schedules as $todaySchedule) {
                foreach($todaySchedule->scheduleDetails as $todayScheduleDetail) {
                    foreach ($todayScheduleDetail->appointments as $todayAppointment) {
                        $combinedAppointment = [
                            'clinic' => $todaySchedule->sc_clinic_name,
                            'doctor' => $todaySchedule->sc_doctor_name,
                            'session' => $todayScheduleDetail->scd_session,
                            'queue' => $todayAppointment->ap_queue,
                            'token' => $todayAppointment->ap_token,
                            'norm' => null,
                            'name' => null,
                            'birthday' => null,
                            'phone' => null,
                            'type' => $todayAppointment->ap_type,
                            'registration_time' => $todayAppointment->ap_registration_time,
                            'appointment_time' => $todayAppointment->ap_appointment_time
                        ];

                        if ($todayAppointment->umumAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->umumAppointment->uap_norm,
                                'name' => $todayAppointment->umumAppointment->uap_name,
                                'birthday' => $todayAppointment->umumAppointment->uap_birthday,
                                'phone' => $todayAppointment->umumAppointment->uap_phone,
                            ]);
                        }

                        if ($todayAppointment->asuransiAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->asuransiAppointment->aap_norm,
                                'name' => $todayAppointment->asuransiAppointment->aap_name,
                                'birthday' => $todayAppointment->asuransiAppointment->aap_birthday,
                                'phone' => $todayAppointment->asuransiAppointment->aap_phone,
                            ]);
                        }

                        if ($todayAppointment->bpjsKesehatanAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->bpjsKesehatanAppointment->bap_norm,
                                'name' => $todayAppointment->bpjsKesehatanAppointment->bap_name,
                                'birthday' => $todayAppointment->bpjsKesehatanAppointment->bap_birthday,
                                'phone' => $todayAppointment->bpjsKesehatanAppointment->bap_phone,
                            ]);
                        }

                        if ($todayAppointment->newAppointment) {
                            $combinedAppointment = array_merge($combinedAppointment, [
                                'norm' => $todayAppointment->newAppointment->nap_norm,
                                'name' => $todayAppointment->newAppointment->nap_name,
                                'birthday' => $todayAppointment->newAppointment->nap_birthday,
                                'phone' => $todayAppointment->newAppointment->nap_phone,
                            ]);
                        }

                        $combinedAppointments[] = $combinedAppointment;
                    }
                }
            }
        }

        usort($combinedAppointments, function ($a, $b) {
            $clinicComparison = strcmp($a['clinic'], $b['clinic']);
            if ($clinicComparison !== 0) {
                return $clinicComparison;
            }

            $doctorComparison = strcmp($a['doctor'], $b['doctor']);
            if ($doctorComparison !== 0) {
                return $doctorComparison;
            }

            $sessionComparison = strcmp($a['session'], $b['session']);
            if ($sessionComparison !== 0) {
                return $sessionComparison;
            }

            return $a['queue'] - $b['queue'];
        });

        $fisioAppointments = FisioterapiAppointment::where('sd_id', (ScheduleDate::where('sd_date', $date)->first()->id))->orderBy('fap_type')->orderBy('fap_queue')->get();

        $data = [
            'title' => $fileName,
            'date' => $date,
            'appointmentData' => $combinedAppointments,
            'fisioData' => $fisioAppointments
        ];

        $pdf = PDF::loadView('backend.appointment.print', $data)->setPaper('a4', 'landscape');
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $fileName . '.pdf');
    }
}
