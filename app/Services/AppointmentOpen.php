<?php

namespace App\Services;

use App\Models\ScheduleDate;

class AppointmentOpen {
    public function selectAppointmentOpen(): bool
    {
        // date_default_timezone_set('Asia/Jakarta');
        // $currentHour = now()->hour;
        // $currentDate = now()->format('Y-m-d');

        // return $currentHour < env('CLOSE_HOUR', 18) && $currentHour >= env('OPEN_HOUR', 7);
        return true;
    }
}
