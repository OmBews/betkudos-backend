<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Soccer;

use App\Models\Markets\Market;
use PHPUnit\Framework\TestCase;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class DoubleChanceCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileDoubleChanceCorrectly()
    {
        $this->assertCompilesMarketCorrectly( 3);
    }

    protected function sampleFile(): string
    {
        return 'Soccer/double_chance.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "10115",
            "name" => "Double Chance",
            "key" => "double_chance",
            "selections" =>  [
                 [
                    "bet365_id" => "1673529052",
                    "name" => "Al Qadisiya Al Khubar or Draw",
                    "odds" => 1.111,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 2,
                    "match_market_id" => 1,
                    "header" => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ],
                [
                    "bet365_id" => "1673529053",
                    "name" => "Al Faisaly Harmah or Draw",
                    "odds" => 1.364,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 2,
                    "match_market_id" => 1,
                    'header' => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ],
                [
                    "bet365_id" => "1673529054",
                    "name" => "Al Qadisiya Al Khubar or Al Faisaly Harmah",
                    "odds" => 2.0,
                    "is_suspended" => false,
                    "order" => "2",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 2,
                    "match_market_id" => 1,
                    'header' => null,
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
        return Market::where('key', 'double_chance')->first();
    }

    protected function teams(): array
    {
        return ['Al Qadisiya Al Khubar', 'Al Faisaly Harmah'];
    }
}
