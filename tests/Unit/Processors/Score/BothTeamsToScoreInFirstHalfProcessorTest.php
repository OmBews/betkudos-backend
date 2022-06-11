<?php

namespace Tests\Unit\Processors\Score;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Score\BothTeamsToScoreInFirstHalfProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class BothTeamsToScoreInFirstHalfProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'both_teams_to_score_in_1st_half')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return BothTeamsToScoreInFirstHalfProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToScoreWon
     */
    public function testWillWonWhenTheSelectionAndResultAreBothTeamsWillScoreByTheFirstHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function bothTeamsToScoreWon()
    {
        return [
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => 'Yes']],
            [['scores' => '{"1":{"home":"1","away":"2"}}'], ['name' => 'Yes']],
            [['scores' => '{"1":{"home":"2","away":"2"}}'], ['name' => 'Yes']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToNotScoreWon
     */
    public function testWillWonWhenTheSelectionAndResultAreBothTeamsWillNotScoreByTheFirstHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function bothTeamsToNotScoreWon()
    {
        return [
            [['scores' => '{"1":{"home":"0","away":"1"}}'], ['name' => 'No']],
            [['scores' => '{"1":{"home":"4","away":"0"}}'], ['name' => 'No']],
            [['scores' => '{"1":{"home":"0","away":"0"}}'], ['name' => 'No']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToScoreLoss
     */
    public function testWillLossWhenTheSelectionIsBothTeamsWillScoreByTheFirstHalfButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function bothTeamsToScoreLoss()
    {
        return [
            [['scores' => '{"1":{"home":"0","away":"1"}}'], ['name' => 'Yes']],
            [['scores' => '{"1":{"home":"4","away":"0"}}'], ['name' => 'Yes']],
            [['scores' => '{"1":{"home":"0","away":"0"}}'], ['name' => 'Yes']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToNotScoreLoss
     */
    public function testWillLossWhenTheSelectionIsBothTeamsWillNotScoreByTheFirstHalfButTheResultsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function bothTeamsToNotScoreLoss()
    {
        return [
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => 'No']],
            [['scores' => '{"1":{"home":"1","away":"2"}}'], ['name' => 'No']],
            [['scores' => '{"1":{"home":"2","away":"2"}}'], ['name' => 'No']],
        ];
    }
}
