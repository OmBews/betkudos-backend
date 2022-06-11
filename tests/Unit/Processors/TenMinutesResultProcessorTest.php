<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\TenMinutesResultProcessor;

class TenMinutesResultProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', '10_minute_result')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return TenMinutesResultProcessor::factory($selection, $market);
    }

    protected function runCustomTestProcessor(array $result, array $odds, array $stats, string $expectedStatus)
    {
        [$selection, $market, , , $match] = $this->buildSelection($result, $odds, $this->market());

        $match->stats()->updateOrCreate(['match_id' => $match->getKey()], $stats);

        $processor = $this->processor($selection, $market);

        $this->assertEquals($expectedStatus, $processor->process());
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider resultIsDraw
     */
    public function testWillWonWhenTheSelectionAndResultAreTheDraw(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_WON);
    }

    public function resultIsDraw()
    {
        return [
            [
                ['single_score' => '0-0'],
                ['name' => 'Draw'],
                ['events' => '[{"text":"0:0 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '1-1'],
                ['name' => 'Draw'],
                ['events' => '[{"text":"1:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider resultIsNotDraw
     */
    public function testWillLoseWhenTheSelectionIsDrawButTheResultIsDifferent(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_LOST);
    }

    public function resultIsNotDraw()
    {
        return [
            [
                ['single_score' => '1-0'],
                ['name' => 'Draw'],
                ['events' => '[{"text":"1:0 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '0-1'],
                ['name' => 'Draw'],
                ['events' => '[{"text":"0:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider homeTeamWins
     */
    public function testWillWonWhenTheSelectionAndResultAreHomeTeam(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_WON);
    }

    public function homeTeamWins()
    {
        return [
            [
                ['single_score' => '3-0'],
                ['name' => 'Home'],
                ['events' => '[{"text":"1:0 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '3-1'],
                ['name' => 'Home'],
                ['events' => '[{"text":"2:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider homeTeamLoses
     */
    public function testWillLosesWhenTheSelectionIsHomeTeamButResultIsDifferent(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_LOST);
    }

    public function homeTeamLoses()
    {
        return [
            [
                ['single_score' => '3-1'],
                ['name' => 'Home'],
                ['events' => '[{"text":"0:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-1'],
                ['name' => 'Home'],
                ['events' => '[{"text":"0:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-1'],
                ['name' => 'Home'],
                ['events' => '[{"text":"0:0 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-1'],
                ['name' => 'Home'],
                ['events' => '[{"text":"1:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider awayTeamWins
     */
    public function testWillWonWhenTheSelectionAndResultAreAwayTeam(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_WON);
    }

    public function awayTeamWins()
    {
        return [
            [
                ['single_score' => '0-3'],
                ['name' => 'Away'],
                ['events' => '[{"text":"0:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '1-3'],
                ['name' => 'Away'],
                ['events' => '[{"text":"1:2 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider awayTeamLoses
     */
    public function testWillLosesWhenTheSelectionIsAwayTeamButResultIsDifferent(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_LOST);
    }

    public function awayTeamLoses()
    {
        return [
            [
                ['single_score' => '3-1'],
                ['name' => 'Away'],
                ['events' => '[{"text":"1:0 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-1'],
                ['name' => 'Away'],
                ['events' => '[{"text":"1:0 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-1'],
                ['name' => 'Away'],
                ['events' => '[{"text":"0:0 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-1'],
                ['name' => 'Away'],
                ['events' => '[{"text":"1:1 Goals 00:00 - 09:59"}]', 'stats' => []]
            ],
        ];
    }
}
