<?php

namespace App\Models\Users\Traits;

use App\Models\Bets\Bet;
use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Sessions\Session;
use App\Models\Users\Devices\Device;
use App\Models\Wallets\Wallet;

trait Relationships
{
    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function notAllowedIp()
    {
        return $this->hasOne(NotAllowedIp::class, 'ip_address', 'ip_address');
    }

    public function sessions()
    {
        return $this->hasMany(Session::class)->orderBy('created_at', 'desc');
    }

    public function session()
    {
        return $this->hasOne(Session::class)->latest();
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function wallets()
    {
        return $this->hasMany(Wallet::class);
    }
}
