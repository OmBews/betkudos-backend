<?php


namespace App\BetsAPI\Bet365;


use App\Contracts\BetsAPI\Bet365\InPlayDataCompilerInterface;
use App\Support\Odds;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InPlayDataCompiler implements InPlayDataCompilerInterface
{
    public Collection $data;

    public function __construct(object $data)
    {
        $this->data = $this->collect($data);
    }

    public function compile(callable $transform, callable $filter = null): InPlayDataCompilerInterface
    {
        $groups = $this->toMarketGroups();

        if ($filter) {
            $groups = $filter($groups);
        }

        $groups = $groups->map(function (Collection $group) use ($transform) {
            $marketGroup = $group->first($this->marketGroupFilter());
            $markets = $group->reject($this->marketGroupFilter());
            $selections = collect([]);

            $hasSubMarkets = $markets->first($this->subMarketsFilter());

            if ($hasSubMarkets) {
                $waitForSubMarkets = false;
                $subMarkets = $markets->filter(function($market) use (&$waitForSubMarkets) {
                    if ($market->type === self::MARKET_DATA_TYPE && property_exists($market, 'NA') && $market->NA === " ") {
                        $waitForSubMarkets = true;
                    } elseif ($market->type === self::MARKET_DATA_TYPE && property_exists($market, 'NA') && $market->NA !== " ") {
                        $waitForSubMarkets = false;
                    }

                    return $waitForSubMarkets && $market->type === self::PARTICIPANT_DATA_TYPE;
                });

                $headersAndSelections = $markets->reject(function ($market) use ($subMarkets) {
                    $isSubMarketIdentifier = $market->type === self::MARKET_DATA_TYPE && property_exists($market, 'NA') &&  $market->NA === " ";
                    $isSubMarket = $market->type === self::PARTICIPANT_DATA_TYPE && $subMarkets->first(fn($m) => $m->IT === $market->IT) !== null;

                    return $isSubMarketIdentifier || $isSubMarket;
                })->chunkWhile(function ($item, $key, $chunk) {
                    $last = $chunk->last();

                    if ($item->type === self::MARKET_DATA_TYPE) {
                        if ($last && $last->type === self::PARTICIPANT_DATA_TYPE) {
                            return false;
                        }
                    }

                    return true;
                });

                //            Resets the array indexes
                $subMarkets = array_values($subMarkets->toArray());

                $headersAndSelections->each(function (Collection $market) use ($marketGroup, $subMarkets, $selections) {
                    $header = $market->first(fn($market) => $market->type === self::MARKET_DATA_TYPE);
                    $participants = $market->filter($this->participantsFilter());
                    //              Resets the array indexes
                    $participants = array_values($participants->toArray());

                    foreach ($subMarkets as $key => $subMarket) {
                        $participant = $participants[$key];

                        $selections->add([
                            'bet365_id' => $participant->ID,
                            'name' => $subMarket->NA,
                            'header' => $header->NA ?? $header->HA ?? $header->HD ?? dd(['id' => $marketGroup->ID, 'name' => $marketGroup->NA, $participant, $participants]),
                            'odds' => Odds::toDecimal($participant->OD),
                            'is_suspended' => $participant->SU === "1",
                            'order' => $participant->OR,
                        ]);
                    }
                });
            } else {
                $participants = $markets->filter($this->participantsFilter());

                $selections = $participants->map(fn($participant) => [
                    'bet365_id' => $participant->ID,
                    'header' => null,
                    'name' => $participant->NA ?? $participant->HA ?? $participant->HD ?? dd(['id' => $marketGroup->ID, 'name' => $marketGroup->NA], $participant, $participants),
                    'odds' => Odds::toDecimal($participant->OD),
                    'is_suspended' => $participant->SU === "1",
                    'order' => $participant->OR,
                ]);
            }

            $market = [
                'id' => $marketGroup->ID,
                'name' => $marketGroup->NA,
                'key' => Str::snake(Str::replaceArray('/', [''], $marketGroup->NA)),
                'selections' => array_values($selections->toArray()),
            ];

            return $transform($market);
        });

        $this->data = $groups;

        return $this;
    }

    private function toMarketGroups(): Collection
    {
        $markets = $this->data->filter(fn ($data) => in_array($data->type, self::MARKET_DATA_TYPES));

        return $markets->chunkWhile(function ($item, $key, $chunk) {
            $last = $chunk->last();

            if ($item->type === self::MARKET_GROUP_DATA_TYPE) {
                if ($last && $last->type === self::PARTICIPANT_DATA_TYPE) {
                    return false;
                }

                return true;
            }

            return $item->type === self::MARKET_DATA_TYPE || $item->type === self::PARTICIPANT_DATA_TYPE;
        });
    }

    private function collect(object $data): Collection
    {
        $collection = collect($data->results);

        return $collection->first() ? collect($collection->first()) : collect([]);
    }

    private function marketGroupFilter(): \Closure
    {
        return fn($data) => $data->type === self::MARKET_GROUP_DATA_TYPE;
    }

    private function subMarketsFilter(): \Closure
    {
        return fn($market) =>
            $market->type === self::MARKET_DATA_TYPE && property_exists($market, 'NA') &&  $market->NA === " ";
    }

    private function participantsFilter(): \Closure
    {
        return fn($participant) =>
            $participant->type === self::PARTICIPANT_DATA_TYPE && property_exists($participant, 'ID');
    }

    public function get(): Collection
    {
        return $this->data;
    }

    public function first()
    {
        return $this->data->first();
    }

    public function toArray(): array
    {
        return $this->data->toArray();
    }
}
