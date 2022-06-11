<?php

namespace App\Models\Sessions\Traits;

use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Sessions\Logs\SessionLog;
use App\Models\Users\Devices\Device;
use App\Models\Users\User;
use Laravel\Passport\Token;

trait Relationships
{
    public function logs()
    {
        return $this->hasMany(SessionLog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function token()
    {
        return $this->hasOne(Token::class, 'id', 'oauth_access_token_id');
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function notAllowedIp()
    {
        return $this->hasOne(NotAllowedIp::class, 'ip_address', 'ip_address');
    }
}
