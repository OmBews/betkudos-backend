<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Soccer;

use App\Models\Markets\Market;
use Database\Seeders\SoccerMarketsSeeder;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class MatchGoalsCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileGoalsOverUnderCorrectly()
    {
        $this->assertCompilesMarketCorrectly(2);
    }

    protected function sampleFile(): string
    {
        return 'Soccer/goals_over_under.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "421",
            "name" => "Match Goals",
            "key" => "match_goals",
            "selections" => [
                [
                    "bet365_id" => "1673827762",
                    "name" => "4.5",
                    "header" => "Over",
                    "odds" => 2.5,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 6,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ],
                [
                    "bet365_id" => "1673827764",
                    "name" => "4.5",
                    "header" => "Under",
                    "odds" => 1.5,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 6,
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
        return Market::where('key', 'goals_over_under')->first();
    }

    protected function teams(): array
    {
        return ['Man Utd', 'Chelsea'];
    }
}
