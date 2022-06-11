<?php

namespace App\Models\Casino\Games;

use App\Models\Wallets\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameSession extends Model
{
    use HasFactory;

    public function getWallet(){
        return $this->hasOne(Wallet::class, 'id', 'wallet');
    }
}
