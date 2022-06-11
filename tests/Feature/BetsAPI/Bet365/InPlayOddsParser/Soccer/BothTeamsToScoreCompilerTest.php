<?php

namespace Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\Soccer;

use App\Models\Markets\Market;
use Tests\Feature\BetsAPI\Bet365\InPlayOddsParser\InPlayOddsCompilerTestCase;

class BothTeamsToScoreCompilerTest extends InPlayOddsCompilerTestCase
{
    public function testWillCompileBothTeamsToScoreCorrectly()
    {
        $this->assertCompilesMarketCorrectly( 2);
    }

    protected function sampleFile(): string
    {
        return 'Soccer/both_teams_to_score.json';
    }

    protected function expected(): array
    {
        return [
            "id" => "50391",
            "name" => "Both Teams to Score",
            "key" => "both_teams_to_score",
            "selections" => [
                [
                    "bet365_id" => "1673529462",
                    "name" => "Yes",
                    "odds" => 4.333,
                    "is_suspended" => false,
                    "order" => "0",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 4,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                    'header' => null
                ],
                [
                    "bet365_id" => "1673529464",
                    "name" => "No",
                    "odds" => 1.2,
                    "is_suspended" => false,
                    "order" => "1",
                    "is_live" => true,
                    "match_id" => 1,
                    "market_id" => 4,
                    "match_market_id" => 1,
                    "transformed" => true,
                    'updated_at' => now()->toDateTimeString(),
                    'header' => null
                ]
            ],
            "transformed" => true,
            "match_market_id" => 1
        ];
    }

    protected function market(): Market
    {
        return Market::where('key', 'both_teams_to_score')->first();
    }

    protected function teams(): array
    {
        return ['Man Utd', 'Chelsea'];
    }
}
