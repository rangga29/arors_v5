<?php

namespace App\Services;

use App\Models\ScheduleDate;
use Carbon\Carbon;

class SCAppointmentDate {
    public function selectSCAppointmentDate()
    {
        $today = Carbon::today();
        $dateData = ScheduleDate::all();

        foreach ($dateData as $date) {
            $dateToCheck = $today->copy()->next(Carbon::SUNDAY);
            if ($date->sd_date == $dateToCheck->format('Y-m-d') && !$date->sd_is_holiday) {
                return $date->sd_date;
            }
        }
        return null;
    }
}
