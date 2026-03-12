<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $fillable = ['scd_id', 'ap_ucode', 'ap_no', 'ap_token', 'ap_queue', 'ap_type', 'ap_registration_time', 'ap_appointment_time'];

    public function getRouteKeyName(): string
    {
        return 'ap_ucode';
    }

    public function scheduleDetail(): BelongsTo
    {
        return $this->belongsTo(ScheduleDetail::class, 'scd_id');
    }

    public function umumAppointment(): HasOne
    {
        return $this->hasOne(UmumAppointment::class, 'ap_id');
    }

    public function asuransiAppointment(): HasOne
    {
        return $this->hasOne(AsuransiAppointment::class, 'ap_id');
    }

    public function bpjsKesehatanAppointment(): HasOne
    {
        return $this->hasOne(BpjsKesehatanAppointment::class, 'ap_id');
    }

    public function newAppointment(): HasOne
    {
        return $this->hasOne(NewAppointment::class, 'ap_id');
    }
}
