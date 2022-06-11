<?php

namespace App\Models\Users\Traits;

use App\Models\Bets\Bet;
use App\Models\Currencies\CryptoCurrency;
use Illuminate\Support\Facades\Hash;

trait Mutators
{
    public function setPasswordAttribute($pass)
    {
        $this->attributes['password'] = Hash::make($pass);
    }

    /**
     * Encrypt the user's google_2fa secret.
     *
     * @param string $value
     * @return void
     */
    public function setGoogle2faSecretAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['google2fa_secret'] = null;
            return;
        }

        $this->attributes['google2fa_secret'] = encrypt($value);
    }

    /**
     * Decrypt the user's google_2fa secret.
     *
     * @param  string  $value
     * @return string
     */
    public function getGoogle2faSecretAttribute($value): string
    {
        return decrypt($value);
    }

    /**
     * Round user balance.
     *
     * @param  float  $value
     * @return float
     */
    public function getBalanceAttribute($value): float
    {
        return round($value, 2);
    }

    public function getGbpBalanceAttribute()
    {
        $balance = 0.00;

        foreach ($this->wallets as $wallet) {
            $balance += $wallet->gbpBalance;
        }

        return $balance;
    }

    public function getEurBalanceAttribute()
    {
        $balance = 0.00;

        foreach ($this->wallets as $wallet) {
            $balance += $wallet->eurBalance;
        }

        return $balance;
    }

    public function getBtcBalanceAttribute()
    {
        return $this->wallets->first(fn ($wallet) => $wallet->currency->ticker === CryptoCurrency::TICKER_BTC)->balance ?? 0.00;
    }

    public function getUsdtBalanceAttribute()
    {
        return $this->wallets->first(fn ($wallet) => $wallet->currency->ticker === CryptoCurrency::TICKER_USDT)->balance ?? 0.00;
    }

    public function getBtcProfitLossAttribute()
    {
        $bets = $this->bets->filter(fn ($bet) => $bet->wallet->currency->ticker === CryptoCurrency::TICKER_BTC);

        $won = $bets
            ->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_WON, Bet::STATUS_HALF_WON]))
            ->sum(fn ($bet) => $bet->profit);

        $staked = $bets
            // ->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_LOST, Bet::STATUS_HALF_LOST]))
            ->sum(fn ($bet) => $bet->stake);

        return $staked - $won;
    }

    public function getUsdtProfitLossAttribute()
    {
        $bets = $this->bets->filter(fn ($bet) => $bet->wallet->currency->ticker === CryptoCurrency::TICKER_USDT);

        $won = $bets
            ->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_WON, Bet::STATUS_HALF_WON]))
            ->sum(fn ($bet) => $bet->profit);

        $staked = $bets
            // ->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_LOST, Bet::STATUS_HALF_LOST]))
            ->sum(fn ($bet) => $bet->stake);

        return $staked - $won;
    }

    public function getGbpProfitLossAttribute()
    {
        $btcGbpPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_BTC)->currency->gbp_price;
        $usdtGbpPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_USDT)->currency->gbp_price;

        $btcGbpProfitLoss = $this->btcProfitLoss * $btcGbpPrice;
        $usdtGbpProfitLoss = $this->usdtProfitLoss * $usdtGbpPrice;

        return $btcGbpProfitLoss + $usdtGbpProfitLoss;
    }

    public function getBtcTotalStakedAttribute()
    {
        return $this->bets
            ->filter(fn ($bet) => $bet->wallet->currency->ticker === CryptoCurrency::TICKER_BTC)
            ->sum(fn ($bet) => $bet->stake);
    }

    public function getUsdtTotalStakedAttribute()
    {
        return $this->bets
            ->filter(fn ($bet) => $bet->wallet->currency->ticker === CryptoCurrency::TICKER_USDT)
            ->sum(fn ($bet) => $bet->stake);
    }

    public function getGbpTotalStakedAttribute()
    {
        $btcGbpPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_BTC)->currency->gbp_price;
        $usdtGbpPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_USDT)->currency->gbp_price;

        $btcGbpTotalStaked = $this->btcTotalStaked * $btcGbpPrice;
        $usdtGbpTotalStaked = $this->usdtTotalStaked * $usdtGbpPrice;

        return $btcGbpTotalStaked + $usdtGbpTotalStaked;
    }

    public function getEurProfitLossAttribute()
    {
        $btcEurPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_BTC)->currency->eur_price;
        $usdtEurPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_USDT)->currency->eur_price;

        $btcEurProfitLoss = $this->btcProfitLoss * $btcEurPrice;
        $usdtEurProfitLoss = $this->usdtProfitLoss * $usdtEurPrice;

        return $btcEurProfitLoss + $usdtEurProfitLoss;
    }

    public function getEurTotalStakedAttribute()
    {
        $btcEurPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_BTC)->currency->eur_price;
        $usdtEurPrice = $this->wallets->first(fn ($wallet) => $wallet->currency->ticker ===  CryptoCurrency::TICKER_USDT)->currency->eur_price;

        $btcEurTotalStaked = $this->btcTotalStaked * $btcEurPrice;
        $usdtEurTotalStaked = $this->usdtTotalStaked * $usdtEurPrice;

        return $btcEurTotalStaked + $usdtEurTotalStaked;
    }
}
