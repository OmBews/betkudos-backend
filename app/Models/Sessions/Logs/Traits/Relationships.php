<?php

namespace App\Models\Sessions\Logs\Traits;

use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Users\User;

trait Relationships
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notAllowedIp()
    {
        return $this->hasOne(NotAllowedIp::class, 'ip_address', 'ip_address');
    }
}
