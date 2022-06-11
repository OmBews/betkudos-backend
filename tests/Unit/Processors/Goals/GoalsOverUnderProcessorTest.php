<?php

namespace Tests\Unit\Processors\Goals;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Goals\GoalsOverUnderProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class GoalsOverUnderProcessorTest extends ProcessorTestCase
{
    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return GoalsOverUnderProcessor::factory($selection, $market);
    }

    protected function market(): Market
    {
        return Market::where('key', 'goals_over_under')->first();
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underWon
     */
    public function testWillWonWhenTheSelectionAndTheResultIsUnder(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function underWon()
    {
        return [
            [['single_score' => '0-0'], ['header' => 'Under', 'name' => '0.5']],

            [['single_score' => '0-0'], ['header' => 'Under', 'name' => '1.5']],
            [['single_score' => '1-0'], ['header' => 'Under', 'name' => '1.5']],

            [['single_score' => '0-0'], ['header' => 'Under', 'name' => '2.5']],
            [['single_score' => '1-0'], ['header' => 'Under', 'name' => '2.5']],
            [['single_score' => '2-0'], ['header' => 'Under', 'name' => '2.5']],

            [['single_score' => '0-0'], ['header' => 'Under', 'name' => '3.5']],
            [['single_score' => '1-0'], ['header' => 'Under', 'name' => '3.5']],
            [['single_score' => '2-0'], ['header' => 'Under', 'name' => '3.5']],
            [['single_score' => '3-0'], ['header' => 'Under', 'name' => '3.5']],

            [['single_score' => '0-0'], ['header' => 'Under', 'name' => '4.5']],
            [['single_score' => '1-0'], ['header' => 'Under', 'name' => '4.5']],
            [['single_score' => '2-0'], ['header' => 'Under', 'name' => '4.5']],
            [['single_score' => '3-0'], ['header' => 'Under', 'name' => '4.5']],
            [['single_score' => '4-0'], ['header' => 'Under', 'name' => '4.5']],

            [['single_score' => '0-0'], ['header' => 'Under', 'name' => '5.5']],
            [['single_score' => '1-0'], ['header' => 'Under', 'name' => '5.5']],
            [['single_score' => '2-0'], ['header' => 'Under', 'name' => '5.5']],
            [['single_score' => '3-0'], ['header' => 'Under', 'name' => '5.5']],
            [['single_score' => '4-0'], ['header' => 'Under', 'name' => '5.5']],
            [['single_score' => '5-0'], ['header' => 'Under', 'name' => '5.5']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overWon
     */
    public function testWillWonWhenTheSelectionAndTheResultIsOver(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function overWon()
    {
        return [
            [['single_score' => '1-0'], ['header' => 'Over', 'name' => '0.5']],
            [['single_score' => '2-0'], ['header' => 'Over', 'name' => '0.5']],

            [['single_score' => '2-0'], ['header' => 'Over', 'name' => '1.5']],
            [['single_score' => '3-0'], ['header' => 'Over', 'name' => '1.5']],

            [['single_score' => '3-0'], ['header' => 'Over', 'name' => '2.5']],
            [['single_score' => '4-0'], ['header' => 'Over', 'name' => '2.5']],

            [['single_score' => '4-0'], ['header' => 'Over', 'name' => '3.5']],
            [['single_score' => '5-0'], ['header' => 'Over', 'name' => '3.5']],

            [['single_score' => '5-0'], ['header' => 'Over', 'name' => '4.5']],
            [['single_score' => '6-0'], ['header' => 'Over', 'name' => '4.5']],

            [['single_score' => '6-0'], ['header' => 'Over', 'name' => '5.5']],
            [['single_score' => '7-0'], ['header' => 'Over', 'name' => '5.5']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overLoss
     */
    public function testWillLossWhenTheSelectionIsOverButTheResultIsUnder(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function overLoss()
    {
        return [
            [['single_score' => '0-0'], ['header' => 'Over', 'name' => '0.5']],

            [['single_score' => '1-0'], ['header' => 'Over', 'name' => '1.5']],

            [['single_score' => '0-0'], ['header' => 'Over', 'name' => '2.5']],
            [['single_score' => '1-0'], ['header' => 'Over', 'name' => '2.5']],
            [['single_score' => '2-0'], ['header' => 'Over', 'name' => '2.5']],

            [['single_score' => '0-0'], ['header' => 'Over', 'name' => '3.5']],
            [['single_score' => '1-0'], ['header' => 'Over', 'name' => '3.5']],
            [['single_score' => '2-0'], ['header' => 'Over', 'name' => '3.5']],
            [['single_score' => '3-0'], ['header' => 'Over', 'name' => '3.5']],

            [['single_score' => '0-0'], ['header' => 'Over', 'name' => '4.5']],
            [['single_score' => '1-0'], ['header' => 'Over', 'name' => '4.5']],
            [['single_score' => '2-0'], ['header' => 'Over', 'name' => '4.5']],
            [['single_score' => '3-0'], ['header' => 'Over', 'name' => '4.5']],
            [['single_score' => '4-0'], ['header' => 'Over', 'name' => '4.5']],

            [['single_score' => '0-0'], ['header' => 'Over', 'name' => '5.5']],
            [['single_score' => '1-0'], ['header' => 'Over', 'name' => '5.5']],
            [['single_score' => '2-0'], ['header' => 'Over', 'name' => '5.5']],
            [['single_score' => '3-0'], ['header' => 'Over', 'name' => '5.5']],
            [['single_score' => '4-0'], ['header' => 'Over', 'name' => '5.5']],
            [['single_score' => '5-0'], ['header' => 'Over', 'name' => '5.5']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underLoss
     */
    public function testWillLossWhenTheSelectionIsUnderButTheResultIsOver(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function underLoss()
    {
        return [
            [['single_score' => '1-0'], ['header' => 'Under', 'name' => '0.5']],

            [['single_score' => '2-0'], ['header' => 'Under', 'name' => '1.5']],
            [['single_score' => '3-0'], ['header' => 'Under', 'name' => '1.5']],

            [['single_score' => '3-0'], ['header' => 'Under', 'name' => '2.5']],
            [['single_score' => '4-0'], ['header' => 'Under', 'name' => '2.5']],

            [['single_score' => '4-0'], ['header' => 'Under', 'name' => '3.5']],
            [['single_score' => '5-0'], ['header' => 'Under', 'name' => '3.5']],

            [['single_score' => '5-0'], ['header' => 'Under', 'name' => '4.5']],
            [['single_score' => '6-0'], ['header' => 'Under', 'name' => '4.5']],

            [['single_score' => '6-0'], ['header' => 'Under', 'name' => '5.5']],
            [['single_score' => '7-0'], ['header' => 'Under', 'name' => '5.5']],
        ];
    }
}
