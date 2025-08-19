<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FisioterapiAppointment extends Model
{
    protected $fillable = ['sd_id', 'fap_ucode', 'fap_token', 'fap_type', 'fap_queue', 'fap_registration_time', 'fap_appointment_time', 'fap_norm', 'fap_name', 'fap_birthday', 'fap_gender', 'fap_phone'];

    public function getRouteKeyName(): string
    {
        return 'fap_ucode';
    }

    public function scheduleDate(): BelongsTo
    {
        return $this->belongsTo(ScheduleDate::class, 'sd_id');
    }
}
