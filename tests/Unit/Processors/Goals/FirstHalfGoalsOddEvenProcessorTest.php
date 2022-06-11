<?php

namespace Tests\Unit\Processors\Goals;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Goals\FirstHalfGoalsOddEvenProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class FirstHalfGoalsOddEvenProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', '1st_half_goals_odd_even')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return FirstHalfGoalsOddEvenProcessor::factory($selection, $market);
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
            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"3","away":"0"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"5","away":"0"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"4","away":"1"}}'], ['name' => 'Odd']],
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
            [['scores' => '{"1":{"home":"2","away":"0"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"4","away":"0"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"2","away":"0"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"3","away":"1"}}'], ['name' => 'Odd']],
            [['scores' => '{"1":{"home":"4","away":"2"}}'], ['name' => 'Odd']],
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
            [['scores' => '{"1":{"home":"2","away":"0"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"2","away":"0"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"4","away":"0"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"2","away":"0"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"4","away":"2"}}'], ['name' => 'Even']],
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
            [['scores' => '{"1":{"home":"2","away":"1"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"2","away":"3"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"4","away":"1"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"2","away":"3"}}'], ['name' => 'Even']],
            [['scores' => '{"1":{"home":"4","away":"3"}}'], ['name' => 'Even']],
        ];
    }
}
