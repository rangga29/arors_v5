<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleDate extends Model
{
    protected $fillable = ['sd_ucode', 'sd_date', 'sd_is_downloaded', 'sd_is_holiday', 'sd_holiday_desc', 'created_by', 'updated_by'];

    public function getRouteKeyName(): string
    {
        return 'sd_ucode';
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'sd_id');
    }

    public function fisioterapiAppointments(): HasMany
    {
        return $this->hasMany(FisioterapiAppointment::class, 'sd_id');
    }
}
