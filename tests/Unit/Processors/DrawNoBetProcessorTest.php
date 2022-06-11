<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\DrawNoBetProcessor;

class DrawNoBetProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'draw_no_bet')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return DrawNoBetProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider finalResultIsDraw
     */
    public function testWillVoidTheBetWhenTheFinalResultIsDraw(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_VOID);
    }

    public function finalResultIsDraw()
    {
        return [
            [['single_score' => '0-0'], ['name' => 'Home']],
            [['single_score' => '1-1'], ['name' => 'Home']],
            [['single_score' => '2-2'], ['name' => 'Home']],

            [['single_score' => '0-0'], ['name' => 'Away']],
            [['single_score' => '1-1'], ['name' => 'Away']],
            [['single_score' => '2-2'], ['name' => 'Away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeWins
     */
    public function testWillWonWhenTheSelectionAndResultIsHome(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeWins()
    {
        return [
            [['single_score' => '1-0'], ['name' => 'Home']],
            [['single_score' => '2-1'], ['name' => 'Home']],
            [['single_score' => '3-0'], ['name' => 'Home']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayWins
     */
    public function testWillWonWhenTheSelectionAndResultIsAway(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayWins()
    {
        return [
            [['single_score' => '0-1'], ['name' => 'Away']],
            [['single_score' => '1-2'], ['name' => 'Away']],
            [['single_score' => '3-4'], ['name' => 'Away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamLoses
     */
    public function testWillLoseWhenTheSelectionIsHomeButTheResultIsAwayTeam(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamLoses()
    {
        return [
            [['single_score' => '1-2'], ['name' => 'Home']],
            [['single_score' => '2-3'], ['name' => 'Home']],
            [['single_score' => '0-1'], ['name' => 'Home']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamLoses
     */
    public function testWillLoseWhenTheSelectionIsAwayButTheResultIsHomeTeam(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamLoses()
    {
        return [
            [['single_score' => '2-1'], ['name' => 'Away']],
            [['single_score' => '1-0'], ['name' => 'Away']],
            [['single_score' => '3-1'], ['name' => 'Away']],
        ];
    }
}
