<?php

namespace App\Jobs\Matches;

use App\BetsAPI\Bet365\InPlayDataCompiler;
use App\Models\Events\Event;
use App\Services\InPlayOddsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use mysql_xdevapi\Exception;

class ProcessLiveOdds implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Event
     */
    private Event $match;

    /**
     * Create a new job instance.
     *
     * @param Event $match
     */
    public function __construct(Event $match)
    {
        $this->match = $match;
    }

    /**
     * Execute the job.
     *
     * @param InPlayOddsService $service
     * @return void|bool
     */
    public function handle(InPlayOddsService $service)
    {
        $data = $service->inPlayData($this->match->getBet365Id());

        if (!$data->success) {
            if (
                $data->error && $data->error === 'PARAM_INVALID' &&
                $this->match->time_status === Event::STATUS_NOT_STARTED
            ) {
                $minutesPassed = (time() - $this->match->starts_at) / 60;

                // Passed 20 minutes after the starts_at date
                if ($minutesPassed >= 20) {
                    return;
                }
            }

            Log::debug("$data->error_detail - {$this->match->bet365_id}");
            return;
        }

        $compiler = new InPlayDataCompiler($data);
        $markets = $this->match->sport->liveMarkets;

        $markets = $compiler
            ->compile($service->transformer($markets, $this->match), $service->marketsFilter($markets))
            ->get()
            ->filter(fn ($market) => $market['transformed']);

        $odds = $markets->map(fn ($market) => $market['selections'])
            ->flatten(1);

        $this->runOddsUpdate($odds->toArray());
        $this->runSuspendUnavailableMarkets($markets, $odds);
    }

    private function runOddsUpdate(array $odds)
    {
        $uniqueKeys = ['match_id', 'market_id', 'bet365_id'];
        $update = ['odds', 'updated_at', 'is_live', 'is_suspended', 'order', 'header'];

        DB::connection(config('database.feed_connection'))->transaction(function () use ($odds, $uniqueKeys, $update) {
            DB::connection(config('database.feed_connection'))->table('odds')->upsert($odds, $uniqueKeys, $update);
        }, 5);
    }

    private function runSuspendUnavailableMarkets(Collection $markets, Collection $odds)
    {
        $availableMarkets = $markets->pluck('match_market_id')->toArray();
        $availableOdds = $odds->pluck('bet365_id')->toArray();

        DB::connection(config('database.feed_connection'))->transaction(function () use ($availableMarkets, $availableOdds) {
            DB::connection(config('database.feed_connection'))
                ->table('odds')
                ->where('match_id', $this->match->getKey())
                ->whereNotIn('match_market_id', $availableMarkets)
                ->where('is_live', true)
                ->update(['is_suspended' => true]);

            DB::connection(config('database.feed_connection'))
                ->table('odds')
                ->where('match_id', $this->match->getKey())
                ->whereIn('match_market_id', $availableMarkets)
                ->whereNotIn('bet365_id', $availableOdds)
                ->where('is_live', true)
                ->update(['is_suspended' => true]);
        }, 5);
    }
}
