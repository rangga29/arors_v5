<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class Clinic extends Model
{
    protected $fillable = ['cl_ucode', 'cl_code', 'cl_code_bpjs', 'cl_name', 'cl_order', 'cl_umum', 'cl_bpjs', 'cl_active', 'created_by', 'updated_by'];

    public function getRouteKeyName(): string
    {
        return 'cl_ucode';
    }
    
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class, 'sc_clinic_code', 'cl_code');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($clinic) {
            $clinic->cl_ucode = Str::random(10);
            $clinic->created_by = Auth::user()->username;
        });
        static::updating(function ($clinic) {
            $clinic->updated_by = Auth::user()->username;
        });
    }
}
