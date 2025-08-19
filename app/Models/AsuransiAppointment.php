<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsuransiAppointment extends Model
{
    protected $fillable = ['ap_id', 'aap_norm', 'aap_name', 'aap_birthday', 'aap_gender', 'aap_phone', 'aap_business_partner_code', 'aap_business_partner_name'];

    public function getRouteKeyName(): string
    {
        return 'id';
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class, 'ap_id');
    }

    public function businessPartner(): BelongsTo
    {
        return $this->belongsTo(BusinessPartner::class, 'bp_id');
    }
}
