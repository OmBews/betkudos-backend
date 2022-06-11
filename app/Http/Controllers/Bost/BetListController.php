<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Casino\Games\CasinoBet;
use Illuminate\Support\Facades\DB as DB;
use Illuminate\Support\Facades\Log;

class BetListController extends Controller
{
    public function bets(Request $request)
    {
        $request->validate([
            'timeframe' => 'nullable|string',
            'filter' => 'nullable|string',
            'search' => 'nullable|string',
            'textsearch' => 'nullable|string',
            'per_page' => 'nullable|integer|in:20,50,200',
            'user_id' => 'nullable|integer',
            'betwin' => 'nullable|string'
        ]);
        
        $perPage = $request->per_page ?? 20;
        $filter = $request->filter;
        $search = $request->search;
        $timeframe = $request->timeframe;
        $textsearch = $request->textsearch;
        
        $betwin = $request->betwin;
        
        try {
            
            $query = CasinoBet::query();

            if ($filter) {
                if ($filter === 'provider') {
                    $query->whereHas('game', function ($query) use ($search) {
                        $query->where('provider', 'LIKE', '%' . $search . '%');
                    });
                }

                if ($textsearch) {
                    $query->whereHas('game', function ($query) use ($textsearch) {
                        $query->where('name', 'LIKE', '%' . $textsearch . '%');
                    });
                }
            }

            if ($request->user_id) {
                $query->where('player_id', $request->user_id);
            }

            $query->select('*');
            $query->selectRaw('COUNT(game_uuid) AS totalgame');
            $query->selectRaw('SUM(amount) AS betAmt');
            $query->where('type', 'bet');
            $query->where('rollback', 0);
            
            if ($timeframe && $timeframe != 'all') {
                $today = date("Y-m-d");
                $bet = new CasinoBet();
                $enddate = $bet->getDateDiff($timeframe);
                $query->whereBetween('created_at', [$enddate . " 00:00:00", $today . " 23:59:59"]);
            }
            
            $query->groupBy('round_id');
            $query->orderBy('id', 'DESC');
            $betList =  $query->with([
                'game', 'user', 'game_session.getWallet.getCrypto',
                'win'
            ])->withCount(['win as win_amount' => function ($q) {
                $q->select(DB::raw('coalesce(SUM(amount),0)'), 0);
            }])->paginate($perPage);

            return [
                'response' => $betList,
                'provider' => CasinoBet::with('game')->groupBy('game_uuid')->get()
            ];
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }
}
