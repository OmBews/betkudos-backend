<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Soccer;

use App\Models\Markets\Market;
use Database\Seeders\SoccerMarketsSeeder;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class AlternativeMatchGoalsCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileAlternativeGoalsOverUnderCorrectly()
    {
        $this->assertCompilesMarketCorrectly(6);
    }

    protected function sampleFile(): string
    {
        return 'Soccer/alternative_goals_over_under.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "17",
            "name" => "Alternative Match Goals",
            "key" => "alternative_match_goals",
            "selections" => [
                [
                    "bet365_id" => "1674155878",
                    "name" => "1.5",
                    "header" => "Over",
                    "odds" => 1.286,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 12,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1674155882",
                    "name" => "3.5",
                    "header" => "Over",
                    "odds" => 5.0,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 12,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1674155884",
                    "name" => "4.5",
                    "header" => "Over",
                    "odds" => 15.0,
                    "is_suspended" => false,
                    "order" => "2",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 12,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1674155879",
                    "name" => "1.5",
                    "header" => "Under",
                    "odds" => 3.5,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 12,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1674155883",
                    "name" => "3.5",
                    "header" => "Under",
                    "odds" => 1.167,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 12,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1674155885",
                    "name" => "4.5",
                    "header" => "Under",
                    "odds" => 1.03,
                    "is_suspended" => false,
                    "order" => "2",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 12,
                    "match_market_id" => 1,
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
        return Market::where('key', 'alternative_total_goals')->first();
    }

    protected function teams(): array
    {
        return ['Man Utd', 'Chelsea'];
    }
}
