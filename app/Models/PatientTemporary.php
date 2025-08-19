<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientTemporary extends Model
{
    protected $table = 'patient_temporary';

    protected $fillable = ['pt_ucode', 'pt_norm', 'pt_name', 'pt_birthday', 'pt_gender', 'pt_ssn', 'pt_poli', 'pt_bpjs', 'pt_ppk1'];

    public function getRouteKeyName(): string
    {
        return 'pt_ucode';
    }
}
