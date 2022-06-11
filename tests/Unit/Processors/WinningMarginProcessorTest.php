<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\WinningMarginProcessor;

class WinningMarginProcessorTest extends ProcessorTestCase
{

    protected function market(): Market
    {
        return Market::where('key', 'winning_margin')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return WinningMarginProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider scoreDrawWon
     */
    public function testWillWonWhenTheSelectionAndResultAreScoreDraw(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function scoreDrawWon()
    {
        return [
            // In this case the selection name will be always "1"
            [['single_score' => '1-1'], ['header' => null, 'name' => '1']],
            [['single_score' => '2-2'], ['header' => null, 'name' => '1']],
            [['single_score' => '3-3'], ['header' => null, 'name' => '1']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider scoreDrawLoses
     */
    public function testWillLoseWhenTheSelectionIsScoreDrawButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function scoreDrawLoses()
    {
        return [
            // In this case the selection name will be always "1"
            [['single_score' => '0-0'], ['header' => null, 'name' => '1']],
            [['single_score' => '1-0'], ['header' => null, 'name' => '1']],
            [['single_score' => '0-1'], ['header' => null, 'name' => '1']],
        ];
    }

    public function testWillWonWhenTheSelectionAndResultIsNoGoals()
    {
        $this->runTestProcessor(
            ['single_score' => '0-0'], ['header' => null, 'name' => '2'], BetSelection::STATUS_WON
        );
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider noGoalsLoses
     */
    public function testWillLoseWhenTheSelectionIsThereIsNoGoalsButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function noGoalsLoses()
    {
        return [
            // In this case the selection name will be always "1"
            [['single_score' => '1-1'], ['header' => null, 'name' => '2']],
            [['single_score' => '1-0'], ['header' => null, 'name' => '2']],
            [['single_score' => '0-1'], ['header' => null, 'name' => '2']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider rightWinningMarginWins
     */
    public function testWillWonWhenTheSelectionIsTheRightWinningMargin(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function rightWinningMarginWins()
    {
        return [
            // Home team + winning margin equals to 1
            [['single_score' => '1-0'], ['header' => '1', 'name' => '1']],
            [['single_score' => '2-1'], ['header' => '1', 'name' => '1']],
            [['single_score' => '3-2'], ['header' => '1', 'name' => '1']],
            // Home team + winning margin equals to 2
            [['single_score' => '2-0'], ['header' => '1', 'name' => '2']],
            [['single_score' => '4-2'], ['header' => '1', 'name' => '2']],
            [['single_score' => '6-4'], ['header' => '1', 'name' => '2']],
            // Home team + winning margin equals to 3
            [['single_score' => '3-0'], ['header' => '1', 'name' => '3']],
            [['single_score' => '6-3'], ['header' => '1', 'name' => '3']],
            [['single_score' => '9-6'], ['header' => '1', 'name' => '3']],
            // Home team + winning margin equals to 4 or more
            [['single_score' => '4-0'], ['header' => '1', 'name' => '4+']],
            [['single_score' => '5-0'], ['header' => '1', 'name' => '4+']],
            [['single_score' => '5-1'], ['header' => '1', 'name' => '4+']],
            [['single_score' => '6-2'], ['header' => '1', 'name' => '4+']],
            [['single_score' => '7-2'], ['header' => '1', 'name' => '4+']],

            // Away team + winning margin equals to 1
            [['single_score' => '0-1'], ['header' => '2', 'name' => '1']],
            [['single_score' => '1-2'], ['header' => '2', 'name' => '1']],
            [['single_score' => '2-3'], ['header' => '2', 'name' => '1']],
            // Away team + winning margin equals to 2
            [['single_score' => '0-2'], ['header' => '2', 'name' => '2']],
            [['single_score' => '2-4'], ['header' => '2', 'name' => '2']],
            [['single_score' => '4-6'], ['header' => '2', 'name' => '2']],
            // Away team + winning margin equals to 3
            [['single_score' => '0-3'], ['header' => '2', 'name' => '3']],
            [['single_score' => '3-6'], ['header' => '2', 'name' => '3']],
            [['single_score' => '6-9'], ['header' => '2', 'name' => '3']],
            // Away team + winning margin equals to 4 or more
            [['single_score' => '0-4'], ['header' => '2', 'name' => '4+']],
            [['single_score' => '0-5'], ['header' => '2', 'name' => '4+']],
            [['single_score' => '1-5'], ['header' => '2', 'name' => '4+']],
            [['single_score' => '2-6'], ['header' => '2', 'name' => '4+']],
            [['single_score' => '2-7'], ['header' => '2', 'name' => '4+']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider wrongWinningMarginLoses
     */
    public function testWillLoseWhenTheSelectionIsTheWrongWinningMargin(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function wrongWinningMarginLoses()
    {
        return [
            // Result Draw | Home team + winning margin equals to 1
            [['single_score' => '0-0'], ['header' => '1', 'name' => '1']],
            // Result Score Draw | Home team + winning margin equals to 1
            [['single_score' => '1-1'], ['header' => '1', 'name' => '1']],
            // Result Away Team | Home team + winning margin equals to 1
            [['single_score' => '0-1'], ['header' => '1', 'name' => '1']],
            [['single_score' => '0-2'], ['header' => '1', 'name' => '1']],

            // Result Draw | Home team + winning margin equals to 2
            [['single_score' => '0-0'], ['header' => '1', 'name' => '2']],
            // Result Score Draw | Home team + winning margin equals to 2
            [['single_score' => '1-1'], ['header' => '1', 'name' => '2']],
            // Result Away Team | Home team + winning margin equals to 2
            [['single_score' => '0-1'], ['header' => '1', 'name' => '2']],
            [['single_score' => '0-2'], ['header' => '1', 'name' => '2']],

            // Result Draw | Home team + winning margin equals to 3
            [['single_score' => '0-0'], ['header' => '1', 'name' => '3']],
            // Result Score Draw | Home team + winning margin equals to 3
            [['single_score' => '1-1'], ['header' => '1', 'name' => '1']],
            // Result Away Team | Home team + winning margin equals to 3
            [['single_score' => '0-1'], ['header' => '1', 'name' => '3']],
            [['single_score' => '0-2'], ['header' => '1', 'name' => '3']],

            // Result Draw | Home team + winning margin equals to 4+
            [['single_score' => '0-0'], ['header' => '1', 'name' => '4+']],
            // Result Score Draw | Home team + winning margin equals to 4+
            [['single_score' => '1-1'], ['header' => '1', 'name' => '4+']],
            // Result Away Team | Home team + winning margin equals to 4+
            [['single_score' => '0-1'], ['header' => '1', 'name' => '4+']],
            [['single_score' => '0-2'], ['header' => '1', 'name' => '4+']],



            // Result Draw | Away team + winning margin equals to 1
            [['single_score' => '0-0'], ['header' => '2', 'name' => '1']],
            // Result Score Draw | Away team + winning margin equals to 1
            [['single_score' => '1-1'], ['header' => '2', 'name' => '1']],
            // Result Home Team | Away team + winning margin equals to 1
            [['single_score' => '1-0'], ['header' => '2', 'name' => '1']],
            [['single_score' => '2-0'], ['header' => '2', 'name' => '1']],

            // Result Draw | Away team + winning margin equals to 2
            [['single_score' => '0-0'], ['header' => '2', 'name' => '2']],
            // Result Score Draw | Away team + winning margin equals to 2
            [['single_score' => '1-1'], ['header' => '2', 'name' => '2']],
            // Result Home Team | Away team + winning margin equals to 2
            [['single_score' => '1-0'], ['header' => '2', 'name' => '2']],
            [['single_score' => '2-0'], ['header' => '2', 'name' => '2']],

            // Result Draw | Away team + winning margin equals to 3
            [['single_score' => '0-0'], ['header' => '2', 'name' => '3']],
            // Result Score Draw | Away team + winning margin equals to 3
            [['single_score' => '1-1'], ['header' => '2', 'name' => '1']],
            // Result Home Team | Away team + winning margin equals to 3
            [['single_score' => '1-0'], ['header' => '2', 'name' => '3']],
            [['single_score' => '2-0'], ['header' => '2', 'name' => '3']],

            // Result Draw | Away team + winning margin equals to 4+
            [['single_score' => '0-0'], ['header' => '2', 'name' => '4+']],
            // Result Score Draw | Away team + winning margin equals to 4+
            [['single_score' => '1-1'], ['header' => '2', 'name' => '4+']],
            // Result Home Team | Away team + winning margin equals to 4+
            [['single_score' => '1-0'], ['header' => '2', 'name' => '4+']],
            [['single_score' => '2-0'], ['header' => '2', 'name' => '4+']],

        ];
    }
}
