<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Tennis;

use App\Models\Markets\Market;
use Database\Seeders\TennisMarketsSeeder;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class TotalGamesCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileTotalGamesCorrectly()
    {
        $this->assertCompilesMarketCorrectly(2);
    }

    protected function sampleFile(): string
    {
        return 'Tennis/total_games.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "130018",
            "name" => "Total Games in Match",
            "key" => "total_games_in_match",
            "selections" => [
                [
                    "bet365_id" => "1692117665",
                    "name" => "34.5",
                    "header" => "Over",
                    "odds" => 1.571,
                    "is_suspended" => false,
                    "order" => 0,
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1606,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),

                ],
                [
                    "bet365_id" => "1692117664",
                    "name" => "34.5",
                    "header" => "Under",
                    "odds" => 2.25,
                    "is_suspended" => false,
                    "is_live" => true,
                    "order" => 1,
                    "match_id" => 1,
                    "market_id" => 1606,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ]
            ],
            "transformed" => true,
            "match_market_id" => 1
        ];
    }

    protected function market(): Market
    {
        return Market::where('key', 'total_games_in_match')->first();
    }

    protected function teams(): array
    {
        return ['Alexander Zverev', 'Ricardas Berankis'];
    }
}
