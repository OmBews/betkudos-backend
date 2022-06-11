<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Models\Casino\Games\CasinoBet;
use App\Models\Casino\Providers\Provider;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Wallets\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommissionController extends Controller
{
    public function getProvider(Request $request)
    {
        $request->validate([
            'provider' => 'String|nullable',
            'period' => 'string|nullable'
        ]);

        $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first();
        $btc = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first();

        $provider = $request->provider;
        $period = $request->period;
        $monthYear = date('Y-m', strtotime($period));

        $queryProvider = Provider::query();

        if ($provider) {
            $queryProvider->where('name', $provider);
        }

        $queryProvider->where('status', 0);
        $queryProvider->orderBy('name');
        $response = $queryProvider->get(['name', 'commission']);

        $dataArr = [];
        foreach ($response as $value) {
            $data = $this->casinoCommissionReport($value->name, $period, $monthYear);
            $usdTotalBet = $data->sum(fn ($w) => $w->usdTotal);
            $usdTotalWin = $data->sum(fn ($w) => $w->usdWinTotal);
            $eurTotalBet = $data->sum(fn ($w) => $w->eurTotal);
            $eurTotalWin = $data->sum(fn ($w) => $w->eurWinTotal);
            $totalGame = $data->sum(fn ($w) => $w->totalgame);
            $freeSpin = $data->sum(fn ($w) => $w->freeSpin);
            $cryptoTotal = $data->sum(fn ($w) => $w->crypto_amt);

            if ($usdTotalBet > $usdTotalWin) {
                $usdPL = $usdTotalBet - $usdTotalWin;
                $color = 'green lighten-3 cell-border';
            } else {
                $usdPL = $usdTotalBet - $usdTotalWin;
                $color = ($usdTotalBet - $usdTotalWin) < 0 ? 'red cell-border' : 'orange cell-border';
            }

            if ($eurTotalBet > $eurTotalWin) {
                $eurPL = $eurTotalBet - $eurTotalWin;
                $eColor = 'green lighten-3 cell-border';
            } else {
                $eurPL = $eurTotalBet - $eurTotalWin;
                $eColor = ($eurTotalBet - $eurTotalWin) < 0 ? 'red cell-border' : 'orange cell-border';
            }

            $eurPLTotal = ($usdPL * $usdt->eur_price) + $eurPL;
            if ($eurPLTotal > 0) {
                $eurPLColor = 'green lighten-3 cell-border';
            } else {
                $eurPLColor = $eurPLTotal < 0 ? 'red cell-border' : 'orange cell-border';
            }

            if ($usdTotalBet || $eurTotalBet) {
                $dataArr[] = [
                    'provider' => $value->name,
                    'commission' => $value->commission,
                    'usdTotalBet' => $usdTotalBet,
                    'usdTotalWin' => $usdTotalWin,
                    'eurTotalBet' => $eurTotalBet,
                    'eurTotalWin' => $eurTotalWin,
                    'totalGame' => $totalGame,
                    'freeSpin' => $freeSpin,
                    'cryptoTotal' => $cryptoTotal,
                    'usdPL' => [
                        'amount' => $usdPL,
                        'color' => $color
                    ],
                    'eurPL' => [
                        'amount' => $eurPL,
                        'eColor' => $eColor
                    ],
                    'eurPLTotal' => [
                        'amount' => $eurPLTotal,
                        'eColor' => $eurPLColor
                    ]
                ];
            }
        }

        return [
            'response' => $dataArr,
            'btc' => $btc,
            'usdt' => $usdt,
            'provider' => Provider::where('status', 0)->orderBy('name')->get('name'),
            'time_period' => $this->getTimePeriod()
        ];
    }

    public function casinoCommissionReport($provider, $period, $monthYear)
    {
        $query = CasinoBet::query();

        $query->select('*');
        $query->selectRaw('COUNT(game_uuid) AS totalgame');
        $query->withCount(['winCountForGameList as usdWinTotal' => function ($q) use ($monthYear, $period) {
            $q->select(DB::raw('coalesce(SUM(amount),0)'));
            $q->where('currency', 'USD');
            if ($period) {
                $q->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m'))"), $monthYear);
            }
        }]);

        $query->withCount(['winCountForGameList as eurWinTotal' => function ($q) use ($monthYear, $period) {
            $q->select(DB::raw('coalesce(SUM(amount),0)'));
            $q->where('currency', 'EUR');
            if ($period) {
                $q->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m'))"), $monthYear);
            }
        }]);

        $query->withCount(['betAmount as usdTotal' => function ($q) use ($monthYear, $period) {
            $q->select(DB::raw('coalesce(SUM(amount),0)'));
            $q->where('currency', 'USD');
            if ($period) {
                $q->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m'))"), $monthYear);
            }
        }]);

        $query->withCount(['betAmount as eurTotal' => function ($q) use ($monthYear, $period) {
            $q->select(DB::raw('coalesce(SUM(amount),0)'));
            $q->where('currency', 'EUR');
            if ($period) {
                $q->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m'))"), $monthYear);
            }
        }]);

        $query->withCount(['betAmount as freeSpin' => function ($q) use ($monthYear, $period) {
            $q->select(DB::raw('coalesce(COUNT(type),0)'));
            $q->where('type', 'freespin');
            if ($period) {
                $q->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m'))"), $monthYear);
            }
        }]);

        if ($provider) {
            $query->whereHas('game', function ($query) use ($provider) {
                $query->where('provider', 'LIKE', '%' . $provider . '%');
            });
        }

        if ($period) {
            $query->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m'))"), $monthYear);
        }

        $query->groupBy('game_uuid');
        $query->with(['casino.gameProvider']);
        $response = $query->get();

        return $response;
    }

    public function getTimePeriod()
    {
        $dates = DB::table('bets')
            ->select(DB::raw("YEAR(created_at) year, MONTH(created_at) month, CONCAT(MONTHNAME(created_at), ' ' ,YEAR(created_at)) timeperiod"))
            ->distinct()
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();
        return $dates;
    }
}
