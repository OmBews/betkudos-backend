<?php

namespace App\Models\Deposits;

use App\Models\Currencies\CryptoCurrency;
use App\Models\kyc\UserKyc;
use App\Models\Users\User;
use App\Models\Wallets\WalletAddress;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deposit extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(WalletAddress::class, 'wallet_address_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id', 'id');
    }

    public function senderAddress()
    {
        return $this->hasOne(UserKyc::class, 'user_id', 'user_id');
    }
}
