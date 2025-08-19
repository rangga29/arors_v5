<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ScheduleDetail extends Model
{
    protected $fillable = ['sc_id', 'scd_session', 'scd_start_time', 'scd_end_time', 'scd_umum', 'scd_bpjs', 'scd_counter_max_umum', 'scd_max_umum', 'scd_counter_max_bpjs', 'scd_max_bpjs', 'scd_counter_online_umum', 'scd_online_umum', 'scd_counter_online_bpjs', 'scd_online_bpjs', 'scd_available'];

    public function getRouteKeyName(): string
    {
        return 'scd_id';
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'sc_id');
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'scd_id');
    }
}
