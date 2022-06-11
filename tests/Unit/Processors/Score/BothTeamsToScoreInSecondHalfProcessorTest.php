<?php

namespace Tests\Unit\Processors\Score;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Score\BothTeamsToScoreInSecondHalfProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class BothTeamsToScoreInSecondHalfProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'both_teams_to_score_in_2nd_half')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return BothTeamsToScoreInSecondHalfProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToScoreWon
     */
    public function testWillWonWhenTheSelectionAndResultAreBothTeamsWillScoreByTheSecondHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function bothTeamsToScoreWon()
    {
        return [
            [['scores' => '{"2":{"home":"1","away":"1"}}'], ['name' => 'Yes']],
            [['scores' => '{"2":{"home":"1","away":"2"}}'], ['name' => 'Yes']],
            [['scores' => '{"2":{"home":"2","away":"2"}}'], ['name' => 'Yes']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToNotScoreWon
     */
    public function testWillWonWhenTheSelectionAndResultAreBothTeamsWillNotScoreByTheSecondHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function bothTeamsToNotScoreWon()
    {
        return [
            [['scores' => '{"2":{"home":"0","away":"1"}}'], ['name' => 'No']],
            [['scores' => '{"2":{"home":"4","away":"0"}}'], ['name' => 'No']],
            [['scores' => '{"2":{"home":"0","away":"0"}}'], ['name' => 'No']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToScoreLoss
     */
    public function testWillLossWhenTheSelectionIsBothTeamsWillScoreByTheSecondHalfButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function bothTeamsToScoreLoss()
    {
        return [
            [['scores' => '{"2":{"home":"0","away":"1"}}'], ['name' => 'Yes']],
            [['scores' => '{"2":{"home":"4","away":"0"}}'], ['name' => 'Yes']],
            [['scores' => '{"2":{"home":"0","away":"0"}}'], ['name' => 'Yes']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToNotScoreLoss
     */
    public function testWillLossWhenTheSelectionIsBothTeamsWillNotScoreByTheSecondHalfButTheResultsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function bothTeamsToNotScoreLoss()
    {
        return [
            [['scores' => '{"2":{"home":"1","away":"1"}}'], ['name' => 'No']],
            [['scores' => '{"2":{"home":"1","away":"2"}}'], ['name' => 'No']],
            [['scores' => '{"2":{"home":"2","away":"2"}}'], ['name' => 'No']],
        ];
    }
}
