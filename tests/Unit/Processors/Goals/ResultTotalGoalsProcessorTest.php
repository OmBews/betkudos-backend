<?php

namespace Tests\Unit\Processors\Goals;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Goals\ResultTotalGoalsProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class ResultTotalGoalsProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'result_total_goals')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return ResultTotalGoalsProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamWonPlusUnder
     */
    public function testWillWonWhenTheSelectionIsHomeTeamAndUnder(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeTeamWonPlusUnder()
    {
        return [
            // Home Team + Under 1.5
            [['single_score' => '1-0'], ['name' => 'Home', 'handicap' => '1.5', 'header' => 'Under']],

            // Home Team + Under 2.5
            [['single_score' => '1-0'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Under']],
            [['single_score' => '2-0'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Under']],

            // Home Team + Under 3.5
            [['single_score' => '1-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '2-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '3-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }


    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamWonButUnderWillLoss
     */
    public function testWillLossWhenTheSelectionIsHomeTeamButTheUnderResultIsLoss(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamWonButUnderWillLoss()
    {
        return [
            // Home Team + Under 1.5
            [['single_score' => '2-0'], ['name' => 'Home', 'handicap' => '1.5', 'header' => 'Under']],

            // Home Team + Under 2.5
            [['single_score' => '3-0'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Under']],

            // Home Team + Under 3.5
            [['single_score' => '4-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underWonButTheHomeTeamLoses
     */
    public function testWillLossWhenTheSelectionIsHomeTeamPlusUnderButTheHomeTeamLoses(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function underWonButTheHomeTeamLoses()
    {
        return [
            // Home Team + Under 1.5
            [['single_score' => '0-1'], ['name' => 'Home', 'handicap' => '1.5', 'header' => 'Under']],

            // Home Team + Under 2.5
            [['single_score' => '0-1'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Under']],
            [['single_score' => '0-2'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Under']],

            // Home Team + Under 3.5
            [['single_score' => '0-1'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '0-2'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '0-3'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamWonPlusUnder
     */
    public function testWillWonWhenTheSelectionIsAwayTeamAndUnder(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayTeamWonPlusUnder()
    {
        return [
            // Away Team + Under 1.5
            [['single_score' => '0-1'], ['name' => 'Away', 'handicap' => '1.5', 'header' => 'Under']],

            // Away Team + Under 2.5
            [['single_score' => '0-1'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Under']],
            [['single_score' => '0-2'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Under']],

            // Away Team + Under 3.5
            [['single_score' => '0-1'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '0-2'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '0-3'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }


    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamWonButUnderWillLoss
     */
    public function testWillLossWhenTheSelectionIsAwayTeamButTheUnderResultIsLoss(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamWonButUnderWillLoss()
    {
        return [
            // Away Team + Under 1.5
            [['single_score' => '2-0'], ['name' => 'Away', 'handicap' => '1.5', 'header' => 'Under']],

            // Away Team + Under 2.5
            [['single_score' => '3-0'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Under']],

            // Away Team + Under 3.5
            [['single_score' => '4-0'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underWonButTheAwayTeamLoses
     */
    public function testWillLossWhenTheSelectionIsAwayTeamPlusUnderButTheAwayTeamLoses(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function underWonButTheAwayTeamLoses()
    {
        return [
            // Away Team + Under 1.5
            [['single_score' => '1-0'], ['name' => 'Away', 'handicap' => '1.5', 'header' => 'Under']],

            // Away Team + Under 2.5
            [['single_score' => '1-0'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Under']],
            [['single_score' => '2-0'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Under']],

            // Away Team + Under 3.5
            [['single_score' => '1-0'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '2-0'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '3-0'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawWonPlusUnder
     */
    public function testWillWonWhenTheSelectionIsTheDrawAndUnder(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function drawWonPlusUnder()
    {
        return [
            // Draw + Under 1.5
            [['single_score' => '0-0'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Under']],

            // Draw + Under 2.5
            [['single_score' => '0-0'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Under']],
            [['single_score' => '1-1'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Under']],

            // Draw + Under 3.5
            [['single_score' => '0-0'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '1-1'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }


    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawWonButUnderWillLoss
     */
    public function testWillLossWhenTheSelectionIsTheDrawButTheUnderResultIsLoss(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function drawWonButUnderWillLoss()
    {
        return [
            // Draw + Under 1.5
            [['single_score' => '1-1'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Under']],

            // Draw + Under 2.5
            [['single_score' => '2-2'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Under']],

            // Draw + Under 3.5
            [['single_score' => '2-2'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underWonButTheDrawLoses
     */
    public function testWillLossWhenTheSelectionIsTheDrawPlusUnderButTheDrawLoses(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function underWonButTheDrawLoses()
    {
        return [
            // Draw + Under 1.5
            [['single_score' => '1-0'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Under']],

            // Draw + Under 2.5
            [['single_score' => '1-0'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Under']],
            [['single_score' => '2-0'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Under']],

            // Draw + Under 3.5
            [['single_score' => '1-0'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '2-0'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Under']],
            [['single_score' => '3-0'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Under']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamWonPlusOver
     */
    public function testWillWonWhenTheSelectionIsHomeTeamAndOver(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeTeamWonPlusOver()
    {
        return [
            // Home Team + Over 1.5
            [['single_score' => '2-0'], ['name' => 'Home', 'handicap' => '1.5', 'header' => 'Over']],

            // Home Team + Over 2.5
            [['single_score' => '3-0'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '4-0'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Over']],

            // Home Team + Over 3.5
            [['single_score' => '4-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '5-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }


    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamWonButOverWillLoss
     */
    public function testWillLossWhenTheSelectionIsHomeTeamButTheOverResultIsLoss(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamWonButOverWillLoss()
    {
        return [
            // Home Team + Over 1.5
            [['single_score' => '1-0'], ['name' => 'Home', 'handicap' => '1.5', 'header' => 'Over']],

            // Home Team + Over 2.5
            [['single_score' => '1-0'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '2-0'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Over']],

            // Home Team + Over 3.5
            [['single_score' => '1-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '2-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '3-0'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overWonButTheHomeTeamLoses
     */
    public function testWillLossWhenTheSelectionIsHomeTeamPlusOverButTheHomeTeamLoses(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function overWonButTheHomeTeamLoses()
    {
        return [
            // Home Team + Over 1.5
            [['single_score' => '0-2'], ['name' => 'Home', 'handicap' => '1.5', 'header' => 'Over']],

            // Home Team + Over 2.5
            [['single_score' => '0-3'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '0-4'], ['name' => 'Home', 'handicap' => '2.5', 'header' => 'Over']],

            // Home Team + Over 3.5
            [['single_score' => '0-4'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '0-5'], ['name' => 'Home', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamWonPlusOver
     */
    public function testWillWonWhenTheSelectionIsAwayTeamAndOver(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayTeamWonPlusOver()
    {
        return [
            // Home Team + Over 1.5
            [['single_score' => '0-2'], ['name' => 'Away', 'handicap' => '1.5', 'header' => 'Over']],

            // Home Team + Over 2.5
            [['single_score' => '0-3'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '0-4'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Over']],

            // Home Team + Over 3.5
            [['single_score' => '0-4'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '0-5'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }


    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamWonButOverWillLoss
     */
    public function testWillLossWhenTheSelectionIsAwayTeamButTheOverResultIsLoss(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamWonButOverWillLoss()
    {
        return [
            // Away Team + Over 1.5
            [['single_score' => '0-1'], ['name' => 'Away', 'handicap' => '1.5', 'header' => 'Over']],

            // Away Team + Over 2.5
            [['single_score' => '0-1'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '0-2'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Over']],

            // Away Team + Over 3.5
            [['single_score' => '0-1'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '0-2'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '0-3'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overWonButTheAwayTeamLoses
     */
    public function testWillLossWhenTheSelectionIsAwayTeamPlusOverButTheAwayTeamLoses(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function overWonButTheAwayTeamLoses()
    {
        return [
            // Home Team + Over 1.5
            [['single_score' => '2-0'], ['name' => 'Away', 'handicap' => '1.5', 'header' => 'Over']],

            // Home Team + Over 2.5
            [['single_score' => '3-0'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '4-0'], ['name' => 'Away', 'handicap' => '2.5', 'header' => 'Over']],

            // Home Team + Over 3.5
            [['single_score' => '4-0'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '5-0'], ['name' => 'Away', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawWonPlusOver
     */
    public function testWillWonWhenTheSelectionIsTheDrawAndOver(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function drawWonPlusOver()
    {
        return [
            // Draw + Over 1.5
            [['single_score' => '1-1'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Over']],
            [['single_score' => '2-2'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Over']],

            // Draw + Over 2.5
            [['single_score' => '2-2'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '3-3'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Over']],

            // Draw + Over 3.5
            [['single_score' => '2-2'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '3-3'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }


    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawWonButUnderWillLoss
     */
    public function testWillLossWhenTheSelectionIsTheDrawButTheOverResultIsLoss(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function drawWonButOverWillLoss()
    {
        return [
            // Draw + Over 1.5
            [['single_score' => '0-0'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Over']],

            // Draw + Over 2.5
            [['single_score' => '0-0'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '1-1'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Over']],

            // Draw + Over 3.5
            [['single_score' => '0-0'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '1-1'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overWonButTheDrawLoses
     */
    public function testWillLossWhenTheSelectionIsTheDrawPlusOverButTheDrawLoses(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function overWonButTheDrawLoses()
    {
        return [
            // Draw + Over 1.5
            [['single_score' => '2-0'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Over']],
            [['single_score' => '3-0'], ['name' => 'Draw', 'handicap' => '1.5', 'header' => 'Over']],

            // Draw + Over 2.5
            [['single_score' => '3-0'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Over']],
            [['single_score' => '4-0'], ['name' => 'Draw', 'handicap' => '2.5', 'header' => 'Over']],

            // Draw + Over 3.5
            [['single_score' => '4-0'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Over']],
            [['single_score' => '5-0'], ['name' => 'Draw', 'handicap' => '3.5', 'header' => 'Over']],
        ];
    }
}
