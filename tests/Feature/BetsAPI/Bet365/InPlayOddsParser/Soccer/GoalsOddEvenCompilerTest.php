<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Soccer;

use App\Models\Markets\Market;
use Database\Seeders\SoccerMarketsSeeder;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class GoalsOddEvenCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileGoalsOddEvenCorrectly()
    {
        $this->assertCompilesMarketCorrectly(2);
    }

    protected function sampleFile(): string
    {
        return 'Soccer/goals_odd_even.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "10562",
            "name" => "Goals Odd/Even",
            "key" => "goals_odd_even",
            "selections" =>  [
                [
                    "bet365_id" => "1673529150",
                    "name" => "Odd",
                    "odds" => 2.375,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 15,
                    "match_market_id" => 1,
                    "header" => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ],
                [
                    "bet365_id" => "1673529151",
                    "name" => "Even",
                    "odds" => 1.533,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 15,
                    "match_market_id" => 1,
                    "header" => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ]
            ],
            'match_market_id' => 1,
            'transformed' => true,
        ];
    }

    protected function market(): Market
    {
        return Market::where('key', 'goals_odd_even')->first();
    }

    protected function teams(): array
    {
        return ['Man Utd', 'Chelsea'];
    }
}
