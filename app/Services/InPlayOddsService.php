<?php

namespace App\Services;

use App\Contracts\BetsAPI\Bet365\Bet365ClientInterface as Bet365Client;
use App\Exceptions\BetsAPI\APICallException;
use App\Models\Events\Event;
use App\Models\Events\MatchMarket;
use App\Models\Markets\Market;
use App\Support\Odds;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InPlayOddsService
{
    /**
     * @var Bet365Client
     */
    private Bet365Client $bet365;

    public function __construct(Bet365Client $client)
    {
        $this->bet365 = $client;
    }

    /**
     * @param $fixtureId
     * @param array $params
     * @return object
     * @throws APICallException
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function inPlayData($fixtureId, array $params = []): object
    {
        $response = $this->bet365->inPlayEvent($fixtureId, $params);

        if ($response->failed()) {
            $response->throw();
        }

        return $response->object();
    }

    public function collectResults(object $data): Collection
    {
        $results = collect($data->results);

        return $results->first() ? collect($results->first()) : collect([]);
    }

    public function marketsFilter(Collection $markets)
    {
        return function (Collection $groups) use ($markets) {
            return $groups->filter(function (Collection $group) use ($markets) {
                $market = $group->first(fn($group) => $group->type === 'MG' && property_exists($group, 'NA'));

                if (!$market) {
                    return false;
                }

                return $markets->first(function(Market $m) use ($market) {
                    $name = $m->name;
                    $key = $m->key;
                    $live_key = $m->live_key;

                    return $name === $market->NA ||
                        $key === Str::snake(Str::replaceArray('/', [''], $market->NA)) ||
                        $live_key === Str::snake(Str::replaceArray('/', [''], $market->NA));
                });
            });
        };
    }

    public function transformer(Collection $markets, Event $event): \Closure
    {
        return function (&$marketData) use($event, $markets) {
            $marketType = $markets->first(function(Market $m) use($marketData) {
                return $m->name === $marketData['name'] || $m->key === $marketData['key'] || $m->live_key === $marketData['key'];
            });

            if ($marketType) {
                $matchMarket = MatchMarket::query()->updateOrCreate([
                    'match_id' => $event->getKey(),
                    'market_id' => $marketType->getKey()
                ], ['order' => $marketType->priority]);

                $matchMarket->setRelation('market', $marketType);

                $marketData['selections'] = $this->fixSelectionsOrder($marketType, $event, $marketData['selections']);
                $marketData['selections'] = array_map($this->selectionTransformer($matchMarket, $event), $marketData['selections']);
                $marketData['match_market_id'] = $matchMarket->getKey();
            }

            $marketData['transformed'] = $marketType !== null;

            return $marketData;
        };
    }

    private function selectionTransformer(MatchMarket $market, Event $event): \Closure
    {
        return function ($selection) use ($market, $event) {
            return array_merge($selection, [
                'match_id' => $event->getKey(),
                'market_id' => $market->market->getKey(),
                'match_market_id' => $market->getKey(),
                'updated_at' => now()->toDateTimeString(),
                'is_live' => true
            ]);
        };
    }

    private function fixSelectionsOrder(Market $market, Event $event, array $selections = []): array
    {
        $totalOfSelections = count($selections);
        $areAllSelectionsOutOfOrder = count(array_filter($selections, fn($s) => $s['order'] === "0")) === $totalOfSelections;

        $teamNamesWithinHeaders = array_filter($selections, function ($selection) use ($event) {
            if (! array_key_exists('header', $selection)) {
                return false;
            }

            return in_array($selection['header'], [$event->home->name, $event->away->name]);
        });

        $areMarketHeadersRelatedToTeamNames = !empty($market->headers) && count($teamNamesWithinHeaders);

        if ($areMarketHeadersRelatedToTeamNames) {
            $collected = collect($selections);

            $ordered = $collected->groupBy("header")->map(function (Collection $selections, $header) use ($event) {

                return $selections->map(function ($selection) use ($header, $event) {
                    $selection['order'] = $header === $event->home->name ? 0 : ($header === $event->away->name ? 1 : 0);

                    return $selection;
                });

            })->flatten(1);

            return $ordered->toArray();
        } elseif ($areAllSelectionsOutOfOrder) {

            if (!empty($market->headers)) {
                return $this->orderSelectionsByMarketHeaders($market->headers, $selections);
            }

            return $selections;
        }

        return $selections;
    }

    private function orderSelectionsByMarketHeaders(array $headers, array $selections): array
    {
        $mapper = function ($selection) use ($headers) {
            $header = $selection['header'];

            if (! in_array($header, $headers)) {
                return $selection;
            }

            $selection['order'] = array_search($header, $headers);

            return $selection;
        };

        return array_map($mapper, $selections);
    }
}
