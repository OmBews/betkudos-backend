<?php

namespace Tests\Unit\Processors\HalfTime;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\HalfTime\HalfTimeDoubleChanceProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class HalfTimeDoubleChanceProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'half_time_double_chance')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return HalfTimeDoubleChanceProcessor::factory($selection, $market);
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
            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => 'Home or Away']],
            [['scores' => '{"1":{"home":"0","away":"2"}}'], ['name' => 'Home or Away']],
            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => '12']],
            [['scores' => '{"1":{"home":"0","away":"2"}}'], ['name' => '12']],
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
            [['scores' => '{"1":{"home":"0","away":"0"}}'], ['name' => 'Home or Away']],
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => 'Home or Away']],
            [['scores' => '{"1":{"home":"0","away":"0"}}'], ['name' => '12']],
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => '12']],
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
            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => 'Home or Draw']],
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => 'Home or Draw']],
            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => '1X']],
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => '1X']],
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
            [['scores' => '{"1":{"home":"0","away":"1"}}'], ['name' => 'Home or Draw']],
            [['scores' => '{"1":{"home":"0","away":"2"}}'], ['name' => '1X']],
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
            [['scores' => '{"1":{"home":"0","away":"1"}}'], ['name' => 'Draw or Away']],
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => 'Draw or Away']],
            [['scores' => '{"1":{"home":"0","away":"1"}}'], ['name' => '2X']],
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => '2X']],
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
            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => 'Draw or Away']],
            [['scores' => '{"1":{"home":"2","away":"0"}}'], ['name' => '2X']],
        ];
    }
}
