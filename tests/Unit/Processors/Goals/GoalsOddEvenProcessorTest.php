<?php

namespace Tests\Unit\Processors\Goals;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Goals\GoalsOddEvenProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class GoalsOddEvenProcessorTest extends ProcessorTestCase
{

    protected function market(): Market
    {
        return Market::where('key', 'goals_odd_even')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return GoalsOddEvenProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider totalOddWins
     */
    public function testWillWonWhenTheSelectionAndTheTotalGoalsIsOdd(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function totalOddWins()
    {
        return [
            [['single_score' => '1-0'], ['name' => 'Odd']],
            [['single_score' => '3-0'], ['name' => 'Odd']],
            [['single_score' => '5-0'], ['name' => 'Odd']],
            [['single_score' => '7-0'], ['name' => 'Odd']],
            [['single_score' => '9-0'], ['name' => 'Odd']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider totalOddLoses
     */
    public function testWillLossWhenTheSelectionIsOddButTheTotalGoalsIsEven(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function totalOddLoses()
    {
        return [
            [['single_score' => '0-0'], ['name' => 'Odd']],
            [['single_score' => '1-1'], ['name' => 'Odd']],
            [['single_score' => '2-0'], ['name' => 'Odd']],
            [['single_score' => '4-0'], ['name' => 'Odd']],
            [['single_score' => '6-0'], ['name' => 'Odd']],
            [['single_score' => '8-0'], ['name' => 'Odd']],
            [['single_score' => '10-0'], ['name' => 'Odd']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider totalEvenWins
     */
    public function testWillWonWhenTheSelectionAndTheTotalGoalsIsEven(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function totalEvenWins()
    {
        return [
            [['single_score' => '2-0'], ['name' => 'Even']],
            [['single_score' => '4-0'], ['name' => 'Even']],
            [['single_score' => '6-0'], ['name' => 'Even']],
            [['single_score' => '8-0'], ['name' => 'Even']],
            [['single_score' => '10-0'], ['name' => 'Even']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider totalEvenLoses
     */
    public function testWillLossWhenTheSelectionIsEvenButTheTotalGoalsIsOdd(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function totalEvenLoses()
    {
        return [
            [['single_score' => '1-0'], ['name' => 'Even']],
            [['single_score' => '2-1'], ['name' => 'Even']],
            [['single_score' => '3-0'], ['name' => 'Even']],
            [['single_score' => '5-0'], ['name' => 'Even']],
            [['single_score' => '7-0'], ['name' => 'Even']],
            [['single_score' => '9-0'], ['name' => 'Even']],
            [['single_score' => '11-0'], ['name' => 'Even']],
        ];
    }
}
