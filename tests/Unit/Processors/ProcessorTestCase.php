<?php


namespace Tests\Unit\Processors;


use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Event;
use App\Models\Events\Results\Result;
use App\Models\Teams\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

abstract class ProcessorTestCase extends TestCase
{
    use RefreshDatabase;

    protected static $selectionNameKey = 'name';

    protected array $defaultOdds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\MarketGroupsTableSeeder::class);
        $this->seed(\SportsTableSeeder::class);
        $this->seed(\SoccerMarketsSeeder::class);
        $this->seed(\FutsalMarketsSeeder::class);
    }

    protected function runTestProcessor(array $result, array $odds, string $expectedStatus)
    {
        [$selection, $market, , ] = $this->buildSelection($result, $odds, $this->market());

        $processor = $this->processor($selection, $market);

        $this->assertEquals($expectedStatus, $processor->process());
    }

    protected function buildSelection(array $result, array $oddsData, Market $market, array $selection = [])
    {
        $match = factory(Event::class)->create();

        $match->home()->save(
            factory(Team::class)->make(['name' => 'Home'])
        );
        $match->away()->save(
            factory(Team::class)->make(['name' => 'Away'])
        );

        $result = array_merge(['bet365_match_id' => Str::random(), 'scores' => '{}'], $result);
        $odds = array_merge(
            ['match_id' => $match->getKey(), 'market_id' => $market->getKey()],
            $oddsData,
            $this->defaultOdds
        );

        $match->result()->save(Result::query()->make($result));
        $odds = factory(MarketOdd::class)->create($odds);

        $selection = array_merge([
            'odd_id' => $odds->getKey(),
            'market_id' => $market->getKey(),
            'match_id' => $match->getKey()
        ], $selection, $oddsData);

        $selection = factory(BetSelection::class)->create($selection);

        return [$selection, $market, $result, $odds, $match];
    }

    protected abstract function market(): Market;

    protected abstract function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor;
}
