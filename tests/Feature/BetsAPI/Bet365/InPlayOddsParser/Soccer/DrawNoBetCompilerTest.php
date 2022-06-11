<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Soccer;

use App\Models\Markets\Market;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class DrawNoBetCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileDrawNoBetCorrectly()
    {
        $this->assertCompilesMarketCorrectly( 2);
    }

    protected function sampleFile(): string
    {
        return 'Soccer/draw_no_bet.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "10563",
            "name" => "Draw No Bet",
            "key" => "draw_no_bet",
            "selections" => [
                [
                    "bet365_id" => "1673529152",
                    "name" => "Al Qadisiya Al Khubar",
                    "odds" => 1.4,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 8,
                    "match_market_id" => 1,
                    "header" => null,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                ],
                [
                    "bet365_id" => "1673529153",
                    "name" => "Al Faisaly Harmah",
                    "odds" => 2.75,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 8,
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

    protected function market(): Market
    {
        return Market::where('key', 'draw_no_bet')->first();
    }

    protected function teams(): array
    {
        return ['Al Qadisiya Al Khubar', 'Al Faisaly Harmah'];
    }
}
