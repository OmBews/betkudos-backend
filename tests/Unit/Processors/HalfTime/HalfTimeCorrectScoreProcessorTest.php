<?php

namespace Tests\Unit\Processors\HalfTime;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\HalfTime\HalfTimeCorrectScoreProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class HalfTimeCorrectScoreProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'half_time_correct_score')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return HalfTimeCorrectScoreProcessor::factory($selection, $market);
    }


    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider rightCorrectScoreWins
     */
    public function testWillWonWhenTheSelectionIsTheRightCorrectScore(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function rightCorrectScoreWins()
    {
        return [
            [['scores' => '{"1":{"home":"0","away":"0"}}'], ['name' => '0-0', 'header' => 'Draw']],
            [['scores' => '{"1":{"home":"1","away":"1"}}'], ['name' => '1-1', 'header' => 'Draw']],
            [['scores' => '{"1":{"home":"2","away":"2"}}'], ['name' => '2-2', 'header' => 'Draw']],
            [['scores' => '{"1":{"home":"3","away":"3"}}'], ['name' => '3-3', 'header' => 'Draw']],

            [['scores' => '{"1":{"home":"1","away":"0"}}'], ['name' => '1-0', 'header' => 'Home']],
            [['scores' => '{"1":{"home":"2","away":"0"}}'], ['name' => '2-0', 'header' => 'Home']],
            [['scores' => '{"1":{"home":"2","away":"1"}}'], ['name' => '2-1', 'header' => 'Home']],
            [['scores' => '{"1":{"home":"3","away":"0"}}'], ['name' => '3-0', 'header' => 'Home']],
            [['scores' => '{"1":{"home":"3","away":"1"}}'], ['name' => '3-1', 'header' => 'Home']],
            [['scores' => '{"1":{"home":"3","away":"2"}}'], ['name' => '3-2', 'header' => 'Home']],

            [['scores' => '{"1":{"home":"0","away":"1"}}'], ['name' => '1-0', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"0","away":"2"}}'], ['name' => '2-0', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"1","away":"2"}}'], ['name' => '2-1', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"0","away":"3"}}'], ['name' => '3-0', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"1","away":"3"}}'], ['name' => '3-1', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"2","away":"3"}}'], ['name' => '3-2', 'header' => 'Away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider wrongCorrectScoreLoses
     */
    public function testWillLossWhenTheSelectionIsTheWrongCorrectScore(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function wrongCorrectScoreLoses()
    {
        return [
            [['scores' => '{"1":{"home":"0","away":"1"}}'], ['name' => '0-0', 'header' => 'Draw']],
            [['scores' => '{"1":{"home":"2","away":"1"}}'], ['name' => '1-1', 'header' => 'Draw']],

            [['scores' => '{"1":{"home":"1","away":"2"}}'], ['name' => '1-0', 'header' => 'Home']],
            [['scores' => '{"1":{"home":"2","away":"2"}}'], ['name' => '2-0', 'header' => 'Home']],

            [['scores' => '{"1":{"home":"2","away":"1"}}'], ['name' => '1-0', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"0","away":"0"}}'], ['name' => '2-0', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"2","away":"2"}}'], ['name' => '2-1', 'header' => 'Away']],
            [['scores' => '{"1":{"home":"4","away":"3"}}'], ['name' => '3-0', 'header' => 'Away']],
        ];
    }
}
