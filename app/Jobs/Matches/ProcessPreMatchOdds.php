<?php

namespace App\Jobs\Matches;

use App\Contracts\Services\BetsAPI\Bet365ServiceInterface;
use App\Exceptions\BetsAPI\APICallException;
use App\Models\Markets\Market;
use App\Models\Markets\MarketGroup;
use App\Models\Events\Event;
use App\Models\Events\MatchMarket;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessPreMatchOdds implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Event
     */
    protected $match;

    private $selectionNamesFixes;

    /**
     * Create a new job instance.
     *
     * @param Event $match
     */
    public function __construct(Event $match)
    {
        $this->match = $match;

        if (config('queue.custom_names') && !$this->queue) {
            $this->onQueue('pre-match-odds');
        }
    }

    /**
     * Execute the job.
     *
     * @param Bet365ServiceInterface $service
     * @return void
     * @throws \Throwable
     */
    public function handle(Bet365ServiceInterface $service)
    {
        $this->selectionNamesFixes = [
            '1' => $this->match->home->name,
            '2' => $this->match->away->name,
            'X' => 'Draw',
            'x' => 'Draw',
        ];

        $response = $service->makePreMatchOddsRequest($this->match->getBet365Id());

        $response = json_decode($response->getBody()->getContents());

        if (!$response->success) {
            throw new APICallException($response->error);
        }

        $results = collect($response->results);

        if (!$event = $results->first()) {
            throw new APICallException(APICallException::UNAVAILABLE_MATCH_RESULT);
        }

        $groups = MarketGroup::activeKeys();

        $activeGroups = [];

        foreach ($event as $key => $value) {
            if (!in_array($key, $groups)) {
                continue;
            }

            if (!isset($value->sp)) {
                continue;
            }

            $activeGroups[$key] = $value->sp;
        }

        $activeGroups = collect($activeGroups);

        $markets = Market::activeKeys($this->match->sport_id);

        $marketKeys = $markets->pluck('key')->toArray();
        $matchOdds = [];

        $activeGroups->each(function ($group) use (&$matchOdds, $markets, $marketKeys) {
            foreach ($group as $marketKey => $market) {
                if (!in_array($marketKey, $marketKeys)) {
                    continue;
                }

                $matchId = $this->match->getKey();
                $marketModel = $markets->where('key', $marketKey)->first();
                $matchMarket = MatchMarket::query()->updateOrCreate([
                    'match_id' => $matchId,
                    'market_id' => $marketModel->id
                ], ['order' => $marketModel->priority]);

                foreach ($market->odds as $selection) {
                    $matchOdds[] = [
                        'market_id' => $marketModel->id,
                        'match_id' => $matchId,
                        'bet365_id' => $selection->id,
                        'odds' => $selection->odds,
                        'name' => $this->selectionName($selection->name ?? null),
                        'header' => $this->selectionName($selection->header ?? null),
                        'handicap' => $selection->handicap ?? null,
                        'match_market_id' => $matchMarket->getKey(),
                    ];
                }
            }
        });

        $matchOdds = collect($matchOdds)->unique('bet365_id')->toArray();

        DB::connection(config('database.feed_connection'))->transaction(function () use ($matchOdds) {
            DB::connection(config('database.feed_connection'))
                ->table('odds')
                ->upsert($matchOdds, ['market_id', 'match_id', 'bet365_id'], ['odds', 'name', 'header', 'handicap']);
        }, 5);
    }

    private function selectionName(string $name = null)
    {
        if (is_null($name)) {
            return null;
        }

        if (array_key_exists($name, $this->selectionNamesFixes)) {
            return $this->selectionNamesFixes[$name];
        }

        return $name;
    }
}
