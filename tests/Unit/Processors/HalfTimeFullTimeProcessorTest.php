<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\HalfTimeFullTimeProcessor;

class HalfTimeFullTimeProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'half_time_full_time')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return HalfTimeFullTimeProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeWinsBothHalves
     */
    public function testWillWonWhenTheSelectionAndResultAreHomeInBothHalves(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeWinsBothHalves()
    {
        $selection = ['name' => 'Home - Home'];

        return [
          [['single_score' => '1-0', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
          [['single_score' => '2-0', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"2","away":"1"}}'], $selection],
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamDoNotWinHalfTime
     */
    public function testWillLoseWhenTheSelectionIsHomeOnBothHalvesButItLosesOnTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamDoNotWinHalfTime()
    {
        $selection = ['name' => 'Home - Home'];

        return [
          [['single_score' => '1-0', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
          [['single_score' => '2-0', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamDoNotWinHalfTime
     */
    public function testWillLoseWhenTheSelectionIsHomeOnBothHalvesButItLosesOnTheFullTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamDoNotWinFullTime()
    {
        $selection = ['name' => 'Home - Home'];

        return [
          [['single_score' => '1-2', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
          [['single_score' => '2-3', 'scores' => '{"1":{"home":"2","away":"1"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"2","away":"1"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayWinsBothHalves
     */
    public function testWillWonWhenTheSelectionAndResultAreAwayInBothHalves(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayWinsBothHalves()
    {
        $selection = ['name' => 'Away - Away'];

        return [
          [['single_score' => '0-1', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
          [['single_score' => '0-2', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
          [['single_score' => '1-2', 'scores' => '{"1":{"home":"1","away":"2"}}'], $selection],
          [['single_score' => '1-2', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamDoNotWinHalfTime
     */
    public function testWillLoseWhenTheSelectionIsAwayOnBothHalvesButItLosesOnTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamDoNotWinHalfTime()
    {
        $selection = ['name' => 'Away - Away'];

        return [
          [['single_score' => '0-1', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
          [['single_score' => '0-2', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
          [['single_score' => '1-2', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamDoNotWinHalfTime
     */
    public function testWillLoseWhenTheSelectionIsAwayOnBothHalvesButItLosesOnTheFullTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamDoNotWinFullTime()
    {
        $selection = ['name' => 'Away - Away'];

        return [
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
          [['single_score' => '3-2', 'scores' => '{"1":{"home":"1","away":"2"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"1","away":"2"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider willDrawInBothHalves
     */
    public function testWillWonWhenTheSelectionAndResultAreTheDrawInBothHalves(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function willDrawInBothHalves()
    {
        $selection = ['name' => 'Draw - Draw'];

        return [
          [['single_score' => '0-0', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawInBothHalvesWillLoseOnFirstHalf
     */
    public function testWillLoseWhenTheSelectionIsTheDrawInBothHalvesButItLosesOnFirstHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function drawInBothHalvesWillLoseOnFirstHalf()
    {
        $selection = ['name' => 'Draw - Draw'];

        return [
          [['single_score' => '1-1', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"2","away":"1"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"1","away":"2"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawInBothHalvesWillLoseOnFullTime
     */
    public function testWillLoseWhenTheSelectionIsTheDrawInBothHalvesButItLosesOnFullTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function drawInBothHalvesWillLoseOnFullTime()
    {
        $selection = ['name' => 'Draw - Draw'];

        return [
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
          [['single_score' => '2-3', 'scores' => '{"1":{"home":"2","away":"2"}}'], $selection],
          [['single_score' => '1-2', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider willDrawInFirstHalfAndHomeTeamWillWonFullTime
     */
    public function testWillWonWhenTheSelectionAndResultAreTheDrawInFirstHalfAndHomeOnFullTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function willDrawInFirstHalfAndHomeTeamWillWonFullTime()
    {
        $selection = ['name' => 'Draw - Home'];

        return [
          [['single_score' => '1-0', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
          [['single_score' => '2-1', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
          [['single_score' => '3-2', 'scores' => '{"1":{"home":"2","away":"2"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider willDrawInFirstHalfAndHomeTeamWillLoseFullTime
     */
    public function testWillLoseWhenTheSelectionIsTheDrawInFirstHalfAndHomeTeamLosesFullTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function willDrawInFirstHalfAndHomeTeamWillLoseFullTime()
    {
        $selection = ['name' => 'Draw - Home'];

        return [
            [['single_score' => '0-1', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
            [['single_score' => '1-2', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
            [['single_score' => '2-3', 'scores' => '{"1":{"home":"2","away":"2"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamWillWonFirstHalfAndDrawInFullTime
     */
    public function testWillWonWhenTheSelectionAndResultAreHomeTemWonFirstHalfAndTheDrawInFullTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeTeamWillWonFirstHalfAndDrawInFullTime()
    {
        $selection = ['name' => 'Home - Draw'];

        return [
          [['single_score' => '1-1', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"2","away":"1"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"2","away":"0"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTemOnFirstHalfAndDrawOnFullTimeWillLose
     */
    public function testWillLoseWhenTheSelectionIsHomeInFirstHalfAndDrawOnFullTimeButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTemOnFirstHalfAndDrawOnFullTimeWillLose()
    {
        $selection = ['name' => 'Home - Draw'];

        return [
            [['single_score' => '0-1', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
            [['single_score' => '0-1', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
            [['single_score' => '1-2', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
            [['single_score' => '3-2', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
            [['single_score' => '2-3', 'scores' => '{"1":{"home":"2","away":"2"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamWillWonFirstHalfAndDrawInFullTime
     */
    public function testWillWonWhenTheSelectionAndResultAreAwayTeamWonFirstHalfAndTheDrawInFullTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayTeamWillWonFirstHalfAndDrawInFullTime()
    {
        $selection = ['name' => 'Away - Draw'];

        return [
          [['single_score' => '1-1', 'scores' => '{"1":{"home":"0","away":"1"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"1","away":"2"}}'], $selection],
          [['single_score' => '2-2', 'scores' => '{"1":{"home":"0","away":"2"}}'], $selection],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamOnFirstHalfAndDrawOnFullTimeWillLose
     */
    public function testWillLoseWhenTheSelectionIsAwayInFirstHalfAndDrawOnFullTimeButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamOnFirstHalfAndDrawOnFullTimeWillLose()
    {
        $selection = ['name' => 'Away - Draw'];

        return [
            [['single_score' => '1-0', 'scores' => '{"1":{"home":"0","away":"0"}}'], $selection],
            [['single_score' => '1-0', 'scores' => '{"1":{"home":"1","away":"0"}}'], $selection],
            [['single_score' => '2-1', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
            [['single_score' => '2-3', 'scores' => '{"1":{"home":"1","away":"1"}}'], $selection],
            [['single_score' => '3-2', 'scores' => '{"1":{"home":"2","away":"2"}}'], $selection],
        ];
    }
}
