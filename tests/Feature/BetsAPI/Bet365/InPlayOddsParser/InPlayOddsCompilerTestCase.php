<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser;

use App\BetsAPI\Bet365\InPlayDataCompiler;
use App\Models\Events\Event;
use App\Models\Markets\Market;
use App\Models\Teams\Team;
use App\Services\InPlayOddsService;
use Database\Seeders\SoccerMarketsSeeder;
use Database\Seeders\TennisMarketsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

abstract class InPlayOddsCompilerTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SoccerMarketsSeeder::class);
        $this->seed(TennisMarketsSeeder::class);
    }

    protected const SAMPLES_DIR = __DIR__ . '/Samples/';

    protected function assertCompilesMarketCorrectly(int $selectionsCount, callable $callback = null)
    {
        $data = json_decode(file_get_contents(self::SAMPLES_DIR.$this->sampleFile()));

        $compiler = new InPlayDataCompiler($data);
        $service = app()->make(InPlayOddsService::class);

        $event = factory(Event::class)->create();

        [$homeTeamName, $awayTeamName] = $this->teams();

        $homeTeam = factory(Team::class)->create(['name' => $homeTeamName]);
        $awayTeam = factory(Team::class)->create(['name' => $awayTeamName]);

        $event->setRelation('home', $homeTeam);
        $event->setRelation('away', $awayTeam);

        $market = $compiler
            ->compile($service->transformer($this->markets(), $event))
            ->first();

        $this->assertMarketDataStructure($market);

        if ($callback) {
            $callback($market);
        }

        $market = $this->transform()($market);

        $this->assertCount($selectionsCount, $market['selections']);
        $this->assertSelectionsWasTransformed($market, $this->parsedKeys());

        $this->assertEquals($this->expected(), $market);
    }

    protected function transform(): \Closure
    {
        return function($market) {
            $market['selections'] = array_map(
                fn($selection) => array_merge($selection, ['transformed' => true]),
                $market['selections']
            );

            return $market;
        };
    }

    protected function parsedKeys(): array
    {
        return [
            'bet365_id', 'name', 'odds',
            'is_suspended', 'order'
        ];
    }

    protected function assertMarketDataStructure(array $market)
    {
        $this->assertArrayHasKey('id', $market);
        $this->assertArrayHasKey('key', $market);
        $this->assertArrayHasKey('name', $market);
        $this->assertArrayHasKey('selections', $market);
    }

    protected function assertSelectionsWasTransformed(array $market, array $keys = [])
    {
        foreach ($market['selections'] as $selection) {
            $this->assertArrayHasKey('transformed', $selection);

            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $selection);
            }
        }
    }

    protected abstract function sampleFile(): string;

    protected abstract function expected(): array;

    protected function market(): Market
    {
        return Market::where('key', 'total_games_in_match')->first();
    }

    protected function markets(): Collection
    {
        return collect([$this->market()]);
    }

    protected abstract function teams(): array;
}
