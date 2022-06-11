<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\DoubleChanceProcessor;

class DoubleChanceProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'double_chance')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return DoubleChanceProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeOrAwayWillWon
     */
    public function testWillWonWhenTheSelectionAndResultAreHomeOrAwayToWin(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeOrAwayWillWon()
    {
        return [
            [['single_score' => '1-0'], ['name' => 'Home or Away']],
            [['single_score' => '0-2'], ['name' => 'Home or Away']],
            [['single_score' => '1-0'], ['name' => '12']],
            [['single_score' => '0-2'], ['name' => '12']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeOrAwayWillLose
     */
    public function testWillLoseWhenTheSelectionIsHomeOrAwayToWinAndTheResultIsDraw(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeOrAwayWillLose()
    {
        return [
            [['single_score' => '0-0'], ['name' => 'Home or Away']],
            [['single_score' => '1-1'], ['name' => 'Home or Away']],
            [['single_score' => '0-0'], ['name' => '12']],
            [['single_score' => '1-1'], ['name' => '12']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamToWinOrDraw
     */
    public function testWillWonWhenTheSelectionAndTheResultAreHomeTeamToWinOrTheDraw(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeTeamToWinOrDraw()
    {
        return [
            [['single_score' => '1-0'], ['name' => 'Home or Draw']],
            [['single_score' => '1-1'], ['name' => 'Home or Draw']],
            [['single_score' => '1-0'], ['name' => '1X']],
            [['single_score' => '1-1'], ['name' => '1X']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamToWinOrDrawWillLose
     */
    public function testWillLoseWhenTheSelectionIsHomeTeamToWinOrTheDrawAndTheResultIsAwayTeam(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamToWinOrDrawWillLose()
    {
        return [
            [['single_score' => '0-1'], ['name' => 'Home or Draw']],
            [['single_score' => '0-2'], ['name' => '1X']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawOrAwayTeamWillWin
     */
    public function testWillWonWhenTheSelectionAndTheResultAreTheDrawOrTheAwayTeamToWin(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function drawOrAwayTeamWillWin()
    {
        return [
            [['single_score' => '0-1'], ['name' => 'Draw or Away']],
            [['single_score' => '1-1'], ['name' => 'Draw or Away']],
            [['single_score' => '0-1'], ['name' => '2X']],
            [['single_score' => '1-1'], ['name' => '2X']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawOrAwayTeamWillLose
     */
    public function testWillLoseWhenTheSelectionIsDrawOrAwayTeamToWinAndTheResultHomeTeam(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function drawOrAwayTeamWillLose()
    {
        return [
            [['single_score' => '1-0'], ['name' => 'Draw or Away']],
            [['single_score' => '2-0'], ['name' => '2X']],
        ];
    }
}
