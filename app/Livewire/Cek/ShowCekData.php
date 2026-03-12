<?php

namespace App\Livewire\Cek;

use App\Models\Schedule;
use App\Models\ScheduleDate;
use App\Models\ScheduleDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Livewire\Component;

class ShowCekData extends Component
{
    public $appointmentList = [];

    public function render()
    {
        View::share('type', 'cek');
        return view('livewire.cek.show-cek-data')
            ->layout('frontend.layout', [
                'subTitle' => 'Cek Nomor Antrian Norm',
                'description' => 'Cek Nomor Antrian Norm Registrasi Online Rumah Sakit Cahya Kawaluyan',
                'subKeywords' => 'cek nomor antrian, cek antrian, cek antrian norm, cek nomor antrian norm'
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
}
