<?php


namespace App\Services;


use App\Models\Currencies\CryptoCurrency;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use Illuminate\Database\Eloquent\Model;

class WalletService
{
    public function createWallet(User $user, CryptoCurrency $cryptoCurrency): Wallet|Model
    {
        $wallet = $user->wallets()->where('crypto_currency_id', $cryptoCurrency->getKey())->first();

        if ($wallet) {
            return $wallet;
        }

        $wallet = new Wallet();

        $wallet->user_id = $user->getKey();
        $wallet->crypto_currency_id = $cryptoCurrency->getKey();
        $wallet->balance = 0;
        $wallet->save();

        return $wallet;
    }
}
