<?php

namespace App\Models\NotAllowedIps;

use Illuminate\Database\Eloquent\Model;

class NotAllowedIp extends Model
{
    protected $fillable = [
        'ip_address'
    ];
}
