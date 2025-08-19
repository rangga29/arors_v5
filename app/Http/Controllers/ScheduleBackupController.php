<?php

namespace App\Http\Controllers;

use App\Models\AppointmentBackup;
use App\Models\FisioterapiAppointmentBackup;
use App\Models\ScheduleBackup;
use App\Models\ScheduleDateBackup;
use App\Models\ScheduleDetailBackup;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScheduleBackupController extends Controller
{
    public function index()
    {
        $this->authorize('viewHistory', ScheduleDateBackup::class);

        return view('backend.schedules.view-backup-date', [
            'schedule_dates' => ScheduleDateBackup::orderBy('sdb_date', 'DESC')->get(),
            'schedule_date_first' => ScheduleDateBackup::orderBy('sdb_date', 'ASC')->first()->sdb_date,
            'schedule_date_last' => ScheduleDateBackup::orderBy('sdb_date', 'DESC')->first()->sdb_date
        ]);
    }

    public function view($date)
    {
        $this->authorize('viewHistory', ScheduleBackup::class);

        return view('backend.schedules.view-backup', [
            'date_original' => $date,
            'date' => Carbon::parse($date)->isoFormat('dddd, DD MMMM YYYY'),
            'schedule_date_first' => ScheduleDateBackup::orderBy('sdb_date', 'ASC')->first()->sdb_date,
            'schedule_date_last' => ScheduleDateBackup::orderBy('sdb_date', 'DESC')->first()->sdb_date,
            'schedules' => ScheduleBackup::where('sdb_id', ScheduleDateBackup::where('sdb_date', $date)->first()->id)
                ->orderBy('scb_clinic_name')
                ->orderBy('scb_doctor_name')
                ->get()
        ]);
    }

    public function showRedirect(Request $request)
    {
        $this->authorize('viewHistory', ScheduleBackup::class);

        return redirect()->route('schedules.backup', $request['schedule-date']);
    }

    public function viewAppointment($date, $clinic, $doctor, $session)
    {
        $this->authorize('viewHistory', ScheduleBackup::class);

        $date_original = $date;
        $date = Carbon::parse($date)->isoFormat('dddd, DD MMMM YYYY');
        $schedule_date_first = ScheduleDateBackup::orderBy('sdb_date', 'ASC')->first()->sdb_date;
        $schedule_date_last = ScheduleDateBackup::orderBy('sdb_date', 'DESC')->first()->sdb_date;
        $scheduleDateData = ScheduleDateBackup::where('sdb_date', $date_original)->first();
        $scheduleData = ScheduleBackup::join('schedule_detail_backups', 'schedule_backups.id', '=', 'schedule_detail_backups.scb_id')
            ->where('schedule_backups.sdb_id', $scheduleDateData->id)
            ->where('schedule_backups.scb_clinic_code', $clinic)
            ->where('schedule_backups.scb_doctor_code', $doctor)
            ->where('schedule_detail_backups.scdb_session', $session)
            ->first();
        $scheduleDetailData = ScheduleDetailBackup::where('scb_id', $scheduleData->scb_id)->first();
        $appointmentData = AppointmentBackup::where('scdb_id', $scheduleDetailData->id)->get();

        return view('backend.schedules.view-backup-appointment', [
            'date_original' => $date_original,
            'date' => $date,
            'clinic_code' => $clinic,
            'doctor_code' => $doctor,
            'schedule_date_first' => $schedule_date_first,
            'schedule_date_last' => $schedule_date_last,
            'schedule' => $scheduleData,
            'session' => $session,
            'appointmentData' => $appointmentData
        ]);
    }

    public function viewFisioterapi($date)
    {
        $this->authorize('viewHistory', ScheduleBackup::class);

        $date_original = $date;
        $date = Carbon::parse($date)->isoFormat('dddd, DD MMMM YYYY');
        $appointmentsUmumPagi = FisioterapiAppointmentBackup::where('sdb_id', (ScheduleDateBackup::where('sdb_date', $date_original)->first()->id))
            ->where('fpb_type', 'UMUM PAGI')
            ->orderBy('fpb_queue')->get();

        $appointmentsUmumSore = FisioterapiAppointmentBackup::where('sdb_id', (ScheduleDateBackup::where('sdb_date', $date_original)->first()->id))
            ->where('fpb_type', 'UMUM SORE')
            ->orderBy('fpb_queue')->get();

        $appointmentsBpjsPagi = FisioterapiAppointmentBackup::where('sdb_id', (ScheduleDateBackup::where('sdb_date', $date_original)->first()->id))
            ->where('fpb_type', 'BPJS PAGI')
            ->orderBy('fpb_queue')->get();

        $appointmentsBpjsSore = FisioterapiAppointmentBackup::where('sdb_id', (ScheduleDateBackup::where('sdb_date', $date_original)->first()->id))
            ->where('fpb_type', 'BPJS SORE')
            ->orderBy('fpb_queue')->get();

        return view('backend.schedules.view-backup-fisio-appointment', [
            'date_original' => $date_original,
            'date' => $date,
            'appointmentDataUmumPagi' => $appointmentsUmumPagi,
            'appointmentDataUmumSore' => $appointmentsUmumSore,
            'appointmentDataBpjsPagi' => $appointmentsBpjsPagi,
            'appointmentDataBpjsSore' => $appointmentsBpjsSore
        ]);
    }
}
