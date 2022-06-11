<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Casino\Games\CasinoBet;
use App\Models\Currencies\CryptoCurrency;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Nullable;

class CasinoGameController extends Controller
{
    public function gameList(Request $request)
    {
        $request->validate([
            'timeframe' => 'nullable|string',
            'filter' => 'nullable|string',
            'search' => 'nullable|string',
            'per_page' => 'nullable|integer|in:20,50,200',
            'casino_pl' => 'nullable|string'
        ]);

        $perPage = $request->per_page ?? 20;
        $filter = $request->filter;
        $search = $request->search;
        $timeframe = $request->timeframe;
        $casinoPL = $request->casino_pl;

        try {

            $query = CasinoBet::query();

            if ($filter) {
                if ($filter === 'provider') {
                    $query->whereHas('game', function ($query) use ($search) {
                        $query->where('provider', 'LIKE', '%' . $search . '%');
                    });
                }
            }

            $query->select('*');
            $query->selectRaw('COUNT(game_uuid) AS totalgame');
            $query->withCount(['winCountForGameList as btcWinTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
                $q->where('crypto_currency', 'BTC');
            }]);
            $query->withCount(['winCountForGameList as usdtWinTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
                $q->where('crypto_currency', 'USDT');
            }]);
            $query->withCount(['winCountForGameList as usdWinTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(amount),0)'));
                $q->where('currency', 'USD');
            }]);
            $query->withCount(['winCountForGameList as eurWinTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(amount),0)'));
                $q->where('currency', 'EUR');
            }]);

            $query->withCount(['betAmount as usdTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(amount),0)'));
                $q->where('currency', 'USD');
            }]);
            $query->withCount(['betAmount as eurTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(amount),0)'));
                $q->where('currency', 'EUR');
            }]);
            $query->withCount(['betAmount as usdtTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
                $q->where('crypto_currency', 'USDT');
            }]);
            $query->withCount(['betAmount as btcTotal' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
                $q->where('crypto_currency', 'BTC');
            }]);

            $query->where('type', 'bet');
            $query->where('rollback', 0);
            $query->whereNull('status');

            if ($timeframe && $timeframe != 'all') {
                $today = date("Y-m-d");
                $bet = new CasinoBet();
                $enddate = $bet->getDateDiff($timeframe);
                $query->whereBetween('created_at', [$enddate . " 00:00:00", $today . " 23:59:59"]);
            }

            $query->groupBy('game_uuid');
            $query->orderBy('id', 'desc');
            $response = $query->with('game', 'user')->paginate($perPage);

            $btc = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first(); // Get btc currency
            $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first(); // // Get USDT currency

            return [
                'response' => $response,
                'btc' => $btc,
                'usdt' => $usdt,
                'provider' => $this->getProvider()
            ];
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }

    public function getProvider()
    {
        return CasinoBet::with('game')->groupBy('game_uuid')->get();
    }
}
