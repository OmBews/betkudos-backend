<?php

namespace App\Jobs\Matches;

use App\Models\Events\Event;
use App\Models\Sports\Sport;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SearchForLiveMatches implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Log::debug("Searching live events...");

        $liveEventsCount = Cache::get('live_events_count', 0);

        $matches = $this->getLiveMatches();
        
        $this->respawnDelayed($matches);

        // Log::debug("Current live events on database: {$matches->count()}");

        foreach ($matches as $match) {
            $lock = Cache::lock("match_{$match->getKey()}_live_process");

            if ($lock->get()) {
                LiveMatchWorker::dispatch($match, $lock->owner());
            }
        }

        // Log::debug("Current live events: $liveEventsCount");
        // Log::debug("End of the search");
    }

    private function respawnDelayed(Collection $matches)
    {
        foreach ($matches as $match) {
            /* @var $lastUpdate Carbon */
            $lastUpdate = Cache::get("event_{$match->getKey()}_last_update", now()->subSeconds(16));
            $lock = Cache::lock("match_{$match->getKey()}_live_process");

            if (now()->subSeconds(15)->greaterThan($lastUpdate)) {
                $lock->forceRelease();
                $lock = Cache::lock("match_{$match->getKey()}_live_process");
                LiveMatchWorker::dispatch($match, $lock->owner());
            }
        }
    }

    private function getLiveMatches(): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->queryLiveMatches()->get();
    }

    private function queryLiveMatches(): \Illuminate\Database\Eloquent\Builder
    {
        return Event::query()
            ->where(function (Builder $query) {
                $query->where('starts_at', '>=', strtotime('-10 minutes'))
                    ->where('starts_at', '<=', strtotime('+5 minutes'))
                    ->whereIn('time_status', [Event::STATUS_IN_PLAY, Event::STATUS_NOT_STARTED])
                    ->orWhere('time_status', Event::STATUS_IN_PLAY);
            })
            ->orderBy('time_status', 'DESC')
            ->whereHas('sport', function ($query) {
                $query->onLiveBetting();
            })->whereHas('league', function ($query) {
                $query->whereNotNull('cc');
            })
            ->with([
                'sport', 'result', 'liveMarkets',
                'sport.liveMarkets', 'league', 'home',
                'away'
            ]);
    }
}
