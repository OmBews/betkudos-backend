<?php

namespace App\Http\Controllers\API;

use App\Contracts\Services\FeedService;
use App\Http\Controllers\Controller;
use App\Http\Resources\MatchResource;
use App\Models\Markets\Market;
use App\Models\Events\Event;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MatchController extends Controller
{
    public function odds(Event $match)
    {
        $isLive = $match->time_status === Event::STATUS_IN_PLAY;

        $markets = Market::where('sport_id', $match->sport_id)
                         ->whereHas('odds', function ($query) use ($isLive, $match) {
                            $query->where('match_id', $match->getKey())
                                  ->where('is_live', $isLive);
                         })
                         ->withMatchOdds($match->getKey(), $isLive);

        return response()->json([
            'data' => [
                'id' => $match->getKey(),
                'league' => $match->league,
                'home' => $match->home,
                'away' => $match->away,
                'starts_at' => gmdate("Y-m-d\TH:i:s\Z", $match->starts_at),
                'markets' => $markets->get(),
            ]
        ]);
    }

    public function show(Event $match, FeedService $feedService)
    {
        return new MatchResource($feedService->match($match));
    }
}
