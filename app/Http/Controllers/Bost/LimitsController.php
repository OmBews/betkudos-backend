<?php

namespace App\Http\Controllers\Bost;

use App\Blockchain\CryptoWallet;
use App\Http\Controllers\Controller;
use App\Models\Bets\Bet;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Deposits\Deposit;
use App\Models\Wallets\Wallet;
use App\Models\Withdrawals\Withdrawal;
use Illuminate\Http\Request;

class LimitsController extends Controller
{
    public function index()
    {
        $btc = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first();
        $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first();

        return $btc;

        return [
            'min_bet' => [
                'BTC' => $btc->min_bet,
                'USDT' => $usdt->min_bet,
                'GBP' => $btc->toGbp($btc->min_bet) + $usdt->toGbp($usdt->min_bet)
            ],
            'max_bet' => [
                'BTC' => $btc->max_bet,
                'USDT' => $usdt->max_bet,
                'GBP' => $btc->toGbp($btc->max_bet) + $usdt->toGbp($usdt->max_bet)
            ],
            'min_withdrawal_per_day' => [
                'BTC' => $btc->network_fee,
                'USDT' => $usdt->network_fee,
                'GBP' => $btc->toGbp($btc->network_fee) + $usdt->toGbp($usdt->network_fee)
            ],
            'instant_withdrawal_limit' => [
                'BTC' => $btcMaxWithdrawalPerDay = $usdt->toGbp(Withdrawal::KYC_LIMIT) / $btc->gbp_price,
                'USDT' => Withdrawal::KYC_LIMIT,
                'GBP' => $btc->toGbp($btcMaxWithdrawalPerDay) + $usdt->toGbp(Withdrawal::KYC_LIMIT)
            ],
        ];
    }
}
