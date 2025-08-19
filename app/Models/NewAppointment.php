<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NewAppointment extends Model
{
    protected $fillable = ['ap_id', 'nap_norm', 'nap_name', 'nap_birthday', 'nap_phone', 'nap_ssn', 'nap_gender', 'nap_address', 'nap_email'];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'ap_id');
    }
}
