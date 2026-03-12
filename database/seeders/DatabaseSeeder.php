<?php

namespace Database\Seeders;

use App\Models\ScheduleDate;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ClinicSeeder::class,
            QrCarolusSeeder::class
        ]);

        $currentDate = Carbon::now()->subMonth();
        $endDate = $currentDate->copy()->addMonth(2);

        while ($currentDate <= $endDate) {
            do {
                $ucode = Str::random(20);
                $ucodeCheck = ScheduleDate::where('sd_ucode', $ucode)->exists();
            } while ($ucodeCheck);
            if ($currentDate->dayOfWeek === CarbonInterface::SUNDAY) {
                ScheduleDate::create([
                    'sd_ucode' => $ucode,
                    'sd_date' => $currentDate,
                    'sd_is_downloaded' => false,
                    'sd_is_holiday' => true,
                    'sd_holiday_desc' => 'Hari Minggu',
                    'created_by' => 'administrator',
                    'updated_by' => null,
                ]);
            } else {
                ScheduleDate::create([
                    'sd_ucode' => $ucode,
                    'sd_date' => $currentDate,
                    'sd_is_downloaded' => false,
                    'sd_is_holiday' => false,
                    'sd_holiday_desc' => null,
                    'created_by' => 'administrator',
                    'updated_by' => null,
                ]);
            }
            $currentDate->addDay();
        }
    }
}
