<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UmumAppointmentRegistration extends Model
{
    protected $fillable = ['uap_id', 'uar_ucode', 'uar_no', 'uar_date', 'uar_session', 'uar_time', 'uar_reg_no', 'uar_reg_status', 'uar_queue', 'uar_room'];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function umumAppointment(): BelongsTo
    {
        return $this->belongsTo(UmumAppointment::class, 'uap_id');
    }
}
