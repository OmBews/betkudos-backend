<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Models\Bets\Bet;
use App\Models\Events\Event;
use Illuminate\Http\Request;

class BetMonitorController extends Controller
{
    public function bets(Request $request)
    {
        $request->validate([
            'currency' => 'nullable|in:all,btc,usdt',
            'filter' => 'nullable|string|in:username,id',
            'search' => 'nullable|string',
            'type' => 'nullable|string|in:all,upcoming,live',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|in:20,50,200',
            'user_id' => 'nullable|integer'
        ]);

        $query = Bet::query();

        $currency = $request->currency;
        $filter = $request->filter;
        $search = $request->search;
        $type = $request->type;
        
        $status = explode(',', $request->status);
        
        if ($request->currency && $request->currency != 'all') {
            $query->whereHas('wallet', function ($query) use ($currency) {
                $query->whereHas('currency', function ($query) use ($currency) {
                    $query->where('ticker', $currency);
                });
            });
        }

        if ($filter) {
            if ($filter === 'username') {
                $query->whereHas('user', function ($query) use ($search) {
                    $query->where('username', 'LIKE', '%' . $search . '%');
                });
            } elseif ($filter === 'id') {
                $query->where('code', 'LIKE', '%' . $search . '%');
            }
        }

        if ($type && $type != 'all') {
            $placedOnLiveBetting = $type === 'live';

            $query->where('live', $placedOnLiveBetting);
        }

        if ($request->status > 0 && !in_array("all", $status)) {
            $query->whereIn('status', $status);
        }
        
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $query->orderByDesc('created_at');

        $perPage = $request->per_page ?? 20;

        return $query->with($this->relations())->paginate($perPage);
    }

    public function relations()
    {
        return [
            'user', 'user.session',
            'wallet', 'wallet.currency', 'selections',
            'selections.match', 'selections.match.home', 'selections.match.away',
            'selections.match.league', 'selections.match.league.country', 'selections.match.sport',
            'selections.marketOdd', 'selections.market'
        ];
    }
}
