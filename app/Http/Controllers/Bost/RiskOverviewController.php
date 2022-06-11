<?php

namespace App\Http\Controllers\Bost;

use App\Blockchain\CryptoWallet;
use App\Http\Controllers\Controller;
use App\Models\Bets\Bet;
use App\Models\Casino\Games\CasinoBet;
use App\Models\Casino\Games\CasinoWin;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Deposits\Deposit;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use App\Models\Withdrawals\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class RiskOverviewController extends Controller
{
    /**
     * Get Risk Overview report for BOST section
     */
    public function index()
    {
        try {
            $btc = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first();
            $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first();

            $usdtCasinoData = $this->getCasinoBet(CryptoCurrency::TICKER_USDT);
            $btcCasinoData = $this->getCasinoBet(CryptoCurrency::TICKER_BTC);
            $usdtWinData = $this->getCasinoWin(CryptoCurrency::TICKER_USDT);
            $btcWinData = $this->getCasinoWin(CryptoCurrency::TICKER_BTC);

            $usdtBetAmount = $usdtCasinoData->sum(fn ($casinoBet) => $casinoBet->crypto_amt);
            $btcBetAmount = $btcCasinoData->sum(fn ($casinoBet) => $casinoBet->crypto_amt);
            $usdtWinAmount = $usdtWinData->sum(fn ($casinoWin) => $casinoWin->crypto_amt);
            $btcWinAmount = $btcWinData->sum(fn ($casinoWin) => $casinoWin->crypto_amt);

            $sportBets = Bet::query()
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->count(); // Sports Bets count

            $casinoBets = CasinoBet::query()
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->count(); // Casino Bets count

            $deposits = Deposit::query()
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=', now()->endOfDay())
                ->count(); // Deposits count

            $withdrawal = Withdrawal::query()
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=', now()->endOfDay())
                ->count(); // Withrawal Count

            $registration = User::query()
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=', now()->endOfDay())
                ->count(); // Registration count

            $casinoIndBetters = CasinoBet::query()
                ->distinct('player_id')
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->count(); // Independent betters of casino

            $sportIndBetters = Bet::query()
                ->distinct('user_id')
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '>=',  now()->endOfDay())
                ->count(); // Independent betters of Sports

            $btcCostumerBalances = Wallet::query()->where('crypto_currency_id', $btc->getKey())->sum('balance');
            $usdtCostumerBalances = Wallet::query()->where('crypto_currency_id', $usdt->getKey())->sum('balance');

            $btcCostumerDeposits = Deposit::query()
                ->where('crypto_currency_id', $btc->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->sum('amount');
            $btcDepositors = Deposit::query()
                ->where('crypto_currency_id', $btc->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->distinct('user_id')->count();

            $usdtCostumerDeposits = Deposit::query()
                ->where('crypto_currency_id', $usdt->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->sum('amount');
            $usdtDepositors = Deposit::query()
                ->where('crypto_currency_id', $usdt->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->distinct('user_id')->count();

            $btcCostumerWithdrawals = Withdrawal::query()
                ->where('crypto_currency_id', $btc->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->sum('amount');
            $btcWithdrawalsCostumer = Withdrawal::query()
                ->where('crypto_currency_id', $btc->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->distinct('user_id')->count();
            $usdtCostumerWithdrawals = Withdrawal::query()
                ->where('crypto_currency_id', $usdt->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->sum('amount');

            $usdtWithdrawalsCostumer = Withdrawal::query()
                ->where('crypto_currency_id', $usdt->getKey())
                ->where('status', 2)
                ->where('created_at', '>=',  now()->startOfDay())
                ->where('created_at', '<=',  now()->endOfDay())
                ->distinct('user_id')->count();

            $btcLiabilities = Bet::query()
                ->whereHas('wallet', function ($query) use ($btc) {
                    $query->where('crypto_currency_id', $btc->getKey());
                })
                ->where('status', Bet::STATUS_OPEN)
                ->sum('profit');

            $usdtLiabilities = Bet::query()
                ->whereHas('wallet', function ($query) use ($usdt) {
                    $query->where('crypto_currency_id', $usdt->getKey());
                })
                ->where('status', Bet::STATUS_OPEN)
                ->sum('profit');

            $btcBetsToday = Bet::query()
                ->whereHas('wallet', function ($query) use ($btc) {
                    $query->where('crypto_currency_id', $btc->getKey());
                })
                ->where('created_at', '>=', now()->startOfDay()->toDateTimeString())
                ->where('created_at', '<=', now()->endOfDay()->toDateTimeString())
                ->get();

            $usdtBetsToday = Bet::query()
                ->whereHas('wallet', function ($query) use ($usdt) {
                    $query->where('crypto_currency_id', $usdt->getKey());
                })
                ->where('created_at', '>=', now()->startOfDay()->toDateTimeString())
                ->where('created_at', '<=', now()->endOfDay()->toDateTimeString())
                ->get();

            $btcStakesToday = $btcBetsToday->sum(fn ($bet) => $bet->stake);
            $usdtStakesToday = $usdtBetsToday->sum(fn ($bet) => $bet->stake);

            $btcWonBets = $btcBetsToday->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_WON, Bet::STATUS_HALF_WON]));
            $usdtWonBets = $usdtBetsToday->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_WON, Bet::STATUS_HALF_WON]));

            $btcLostBets = $btcBetsToday->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_LOST, Bet::STATUS_HALF_LOST]));
            $usdtLostBets = $usdtBetsToday->filter(fn ($bet) => in_array($bet->status, [Bet::STATUS_LOST, Bet::STATUS_HALF_LOST]));

            $btcProfitLossToday = $btcLostBets->sum(fn ($bet) => $bet->stake) - $btcWonBets->sum(fn ($bet) => $bet->profit);
            $usdtProfitLossToday = $usdtLostBets->sum(fn ($bet) => $bet->stake) - $usdtWonBets->sum(fn ($bet) => $bet->profit);

            if (App::environment('production')) {
                $cw = new CryptoWallet();
                $btcBalance = ($cw->get_balance(strtolower($btc->ticker)));
                $usdtBalance = ($cw->get_balance(strtolower($usdt->ticker)) - $usdtCostumerBalances) - $usdtLiabilities;
            } else {
                $btcBalance = 0;
                $usdtBalance = 0;
            }

            return [
                'balance' => [
                    'hot_balance' => [
                        'BTC' => $btcBalance,
                        'USDT' => $usdtBalance,
                        'EUR' => ($btcBalance * $btc->eur_price)
                    ],
                    'open_bets_liabilities' => [
                        'BTC' => $btcLiabilities,
                        'USDT' => $usdtLiabilities,
                        'EUR' => ($btcLiabilities * $btc->eur_price)
                    ],
                    'costumer_balances' => [
                        'BTC' => $btcCostumerBalances,
                        'USDT' => $usdtCostumerBalances,
                        'EUR' => ($btcCostumerBalances * $btc->eur_price)
                    ],
                ],
                'total_sum' => [
                    'deposite_total' => [
                        'BTC' => $btcCostumerDeposits,
                        'USDT' => $usdtCostumerDeposits,
                        'EUR' => ($btcCostumerDeposits * $btc->eur_price)
                    ],
                    'deposite_average' => [
                        'BTC' =>  $this->findAvgDeposit($btcCostumerDeposits, $btcDepositors),
                        'USDT' =>  $this->findAvgDeposit($usdtCostumerDeposits, $usdtDepositors),
                        'EUR' => ($this->findAvgDeposit($btcCostumerDeposits, $btcDepositors) * $btc->eur_price)
                    ],
                    'withdrawal_total' => [
                        'BTC' => $btcCostumerWithdrawals,
                        'USDT' => $usdtCostumerWithdrawals,
                        'EUR' => ''
                    ],
                    'withdrawal_average' => [
                        'BTC' => $this->findAvgWithdrawal($btcCostumerWithdrawals, $btcWithdrawalsCostumer),
                        'USDT' => $this->findAvgWithdrawal($usdtCostumerWithdrawals, $usdtWithdrawalsCostumer),
                        'EUR' => ($this->findAvgWithdrawal($btcCostumerWithdrawals, $btcWithdrawalsCostumer) * $btc->eur_price)
                    ],
                    'sports_stake' => [
                        'BTC' => $btcStakesToday,
                        'USDT' => $usdtStakesToday,
                        'EUR' => ($btcStakesToday * $btc->eur_price)
                    ],
                    'sports_pl' => [
                        'BTC' => $btcProfitLossToday,
                        'USDT' => $usdtProfitLossToday,
                        'EUR' => ($btcProfitLossToday * $btc->eur_price)
                    ],
                    'casino_stake' => [
                        'BTC' => $btcBetAmount,
                        'USDT' => $usdtBetAmount,
                        'EUR' => ($btcBetAmount * $btc->eur_price)
                    ],
                    'casino_pl' => [
                        'BTC' => ($btcBetAmount - $btcWinAmount),
                        'USDT' => ($usdtBetAmount - $usdtWinAmount),
                        'EUR' => (($btcBetAmount - $btcWinAmount) * $btc->eur_price)
                    ]
                ],
                'total_count' => [
                    'sports_count' => $sportBets,
                    'casino_count' => $casinoBets,
                    'deposite_count' => $deposits,
                    'withdrawal_count' => $withdrawal,
                    'users_count' => $registration,
                    'ind_betters' => $casinoIndBetters + $sportIndBetters
                ],
                'btc' => $btc,
                'usdt' => $usdt
            ];
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function getCasinoBet($currency)
    {
        $query = CasinoBet::query();
        $query->where('type', 'bet');
        $query->where('crypto_currency', $currency);
        $query->where('created_at', '>=', now()->startOfDay());
        $query->where('created_at', '<=', now()->endOfDay());
        return $query->get();
    }

    public function getCasinoWin($currency)
    {
        $query = CasinoWin::query();
        $query->where('type', 'win');
        $query->where('crypto_currency', $currency);
        $query->where('created_at', '>=', now()->startOfDay());
        $query->where('created_at', '<=', now()->endOfDay());
        return $query->get();
    }

    public function findAvgDeposit($deposite, $depositors)
    {
        if ($deposite > 0 && $depositors > 0) {
            return $deposite / $depositors;
        } else {
            return 0;
        }
    }

    public function findAvgWithdrawal($withdrawal, $withdrawalCus)
    {
        if ($withdrawal > 0 && $withdrawalCus > 0) {
            return $withdrawal / $withdrawalCus;
        } else {
            return 0;
        }
    }
}
