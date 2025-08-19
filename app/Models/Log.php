<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = ['lo_time', 'lo_user', 'lo_ip', 'lo_module', 'lo_message'];

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
