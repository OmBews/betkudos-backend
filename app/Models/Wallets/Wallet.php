<?php

namespace App\Models\Wallets;

use App\Models\Casino\Games\CasinoBet;
use App\Models\Currencies\CryptoCurrency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Wallet
 * @package App\Models\Wallets
 *
 * @property float balance
 * @property CryptoCurrency currency
 */
class Wallet extends Model
{
    use HasFactory;

    public function currency()
    {
        return $this->belongsTo(CryptoCurrency::class, 'crypto_currency_id', 'id');
    }

    public function address()
    {
        return $this->hasOne(WalletAddress::class)->where('manual', 0)->latest();
    }

    public function getGbpBalanceAttribute()
    {
        return $this->balance * $this->currency->gbp_price;
    }

    public function getEurBalanceAttribute()
    {
        return $this->balance * $this->currency->eur_price;
    }

    public function exchangeBalance(string $currency)
    {
        return match ($currency) {
            "USD" => $this->balance * $this->currency->usd_price,

            "GBP" => $this->balance * $this->currency->gbp_price,

            "EUR" => $this->balance * $this->currency->eur_price,

            "BTC" => $this->balance * $this->currency->btc_price,

            default => 0
        };
    }

    public function exchangeValue(string $currency)
    {
        return match ($currency) {
            "USD" => $this->currency->usd_price,

            "GBP" => $this->currency->gbp_price,

            "EUR" => $this->currency->eur_price,

            "BTC" => $this->currency->btc_price,

            default => 0
        };
    }

    public function getCrypto(){
        return $this->hasOne(CryptoCurrency::class, 'id', 'crypto_currency_id');
    }
}
