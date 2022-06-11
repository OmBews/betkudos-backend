<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Tennis;

use App\Models\Markets\Market;
use Database\Seeders\TennisMarketsSeeder;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class ToWinCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileToWinCorrectly()
    {
        $this->assertCompilesMarketCorrectly(4);
    }

    protected function sampleFile(): string
    {
        return 'Tennis/to_win.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "67",
            "name" => "To Win",
            "key" => "to_win",
            "selections" => [
                [
                    "bet365_id" => "1671914066",
                    "name" => "Match",
                    "header" => "Alexander Zverev",
                    "odds" => 1.007,
                    "is_suspended" => false,
                    "order" => 0,
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1605,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1673363933",
                    "name" => "Set 2",
                    "header" => "Alexander Zverev",
                    "odds" => 1.071,
                    "is_suspended" => false,
                    "order" => 0,
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1605,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1671914064",
                    "name" => "Match",
                    "header" => "Ricardas Berankis",
                    "odds" => 29.0,
                    "is_suspended" => false,
                    "order" => 1,
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1605,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ], [
                    "bet365_id" => "1673363907",
                    "name" => "Set 2",
                    "header" => "Ricardas Berankis",
                    "odds" => 9.0,
                    "is_suspended" => false,
                    "order" => 1,
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1605,
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
        return Market::where('key', 'to_win')->first();
    }

    protected function teams(): array
    {
        return ['Alexander Zverev', 'Ricardas Berankis'];
    }
}
