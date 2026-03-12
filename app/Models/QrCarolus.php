<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class QrCarolus extends Model
{
    protected $table = 'qr_caroluses';
    protected $fillable = ['qrc_ucode', 'qrc_room', 'qrc_password', 'qrc_name', 'qrc_order', 'qrc_active', 'created_by', 'updated_by'];

    public function getRouteKeyName(): string
    {
        return 'qrc_ucode';
    }

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($qr_carolus) {
            $qr_carolus->qrc_ucode = Str::random(20);
            $qr_carolus->created_by = Auth::user()->username;
        });
    }
}
