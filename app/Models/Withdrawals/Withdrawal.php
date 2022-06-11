<?php

namespace App\Models\Withdrawals;

use App\Models\Currencies\CryptoCurrency;
use App\Models\kyc\UserKyc;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory;

    public const KYC_LIMIT = 2.5;

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function userKyc()
    {
        return $this->belongsTo(UserKyc::class, 'user_id', 'user_id');
    }

    public function currency()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id', 'id');
    }

    public function getIsAutomaticAttribute()
    {
        $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first();
        $usdLimit = self::KYC_LIMIT;
        $gbpLimit = $usdt->gbp_price * $usdLimit;

        return $this->gbpWithdrawAmount < $gbpLimit;
    }

    public function getGbpWithdrawAmountAttribute()
    {
        return $this->amount * $this->currency->gbp_price;
    }
}
