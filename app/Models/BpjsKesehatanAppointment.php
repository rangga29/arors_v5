<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BpjsKesehatanAppointment extends Model
{
    protected $fillable = ['ap_id', 'bap_norm', 'bap_name', 'bap_birthday', 'bap_gender', 'bap_phone', 'bap_bpjs', 'bap_ppk1'];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'ap_id');
    }
}
