<?php

namespace Tests\Unit\Processors\Score;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Score\BothTeamsToScoreProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class BothTeamsToScoreProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'both_teams_to_score')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return BothTeamsToScoreProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToScoreWon
     */
    public function testWillWonWhenTheSelectionAndResultAreBothTeamsWillScore(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function bothTeamsToScoreWon()
    {
        return [
            [['single_score' => '1-1'], ['name' => 'Yes']],
            [['single_score' => '1-2'], ['name' => 'Yes']],
            [['single_score' => '2-2'], ['name' => 'Yes']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToNotScoreWon
     */
    public function testWillWonWhenTheSelectionAndResultAreBothTeamsWillNotScore(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function bothTeamsToNotScoreWon()
    {
        return [
            [['single_score' => '0-1'], ['name' => 'No']],
            [['single_score' => '4-0'], ['name' => 'No']],
            [['single_score' => '0-0'], ['name' => 'No']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToScoreLoss
     */
    public function testWillLossWhenTheSelectionIsBothTeamsWillScoreButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function bothTeamsToScoreLoss()
    {
        return [
            [['single_score' => '0-1'], ['name' => 'Yes']],
            [['single_score' => '4-0'], ['name' => 'Yes']],
            [['single_score' => '0-0'], ['name' => 'Yes']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider bothTeamsToNotScoreLoss
     */
    public function testWillLossWhenTheSelectionIsBothTeamsWillNotScoreButTheResultsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function bothTeamsToNotScoreLoss()
    {
        return [
            [['single_score' => '1-1'], ['name' => 'No']],
            [['single_score' => '1-2'], ['name' => 'No']],
            [['single_score' => '2-2'], ['name' => 'No']],
        ];
    }
}
