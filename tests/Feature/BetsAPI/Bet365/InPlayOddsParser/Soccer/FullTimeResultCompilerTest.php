<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Soccer;

use App\Models\Markets\Market;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class FullTimeResultCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileFullTimeResultCorrectly()
    {
        $this->assertCompilesMarketCorrectly(3);
    }

    protected function expected(): array
    {
        return [
            "id" => "1777",
            "name" => "Fulltime Result",
            "key" => "fulltime_result",
            "selections" =>  [
                [
                    "bet365_id" => "1670371468",
                    "name" => "FK Taraz",
                    "odds" => 1.002,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1,
                    "match_market_id" => 1,
                    "header" => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ],
                [
                    "bet365_id" => "1670371484",
                    "name" => "Draw",
                    "odds" => 51.0,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1,
                    "match_market_id" => 1,
                    "header" => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ],
                [
                    "bet365_id" => "1670371488",
                    "name" => "FK Aktobe",
                    "odds" => 81.0,
                    "is_suspended" => false,
                    "order" => "2",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 1,
                    "match_market_id" => 1,
                    "header" => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ]
            ],
            "transformed" => true,
            "match_market_id" => 1
        ];
    }

    protected function sampleFile(): string
    {
        return 'Soccer/full_time_result.json';
    }

    protected function market(): Market
    {
        return Market::where('key', 'full_time_result')->first();
    }

    protected function teams(): array
    {
        return ['FK Taraz', 'FK Aktobe'];
    }
}
