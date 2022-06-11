<?php

namespace Tests\Unit\Processors\Score;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Score\CorrectScoreProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class CorrectScoreProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'correct_score')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return CorrectScoreProcessor::factory($selection, $market);
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
            [['single_score' => '0-0'], ['name' => '0-0', 'header' => 'Draw']],
            [['single_score' => '1-1'], ['name' => '1-1', 'header' => 'Draw']],
            [['single_score' => '2-2'], ['name' => '2-2', 'header' => 'Draw']],
            [['single_score' => '3-3'], ['name' => '3-3', 'header' => 'Draw']],

            [['single_score' => '1-0'], ['name' => '1-0', 'header' => 'Home']],
            [['single_score' => '2-0'], ['name' => '2-0', 'header' => 'Home']],
            [['single_score' => '2-1'], ['name' => '2-1', 'header' => 'Home']],
            [['single_score' => '3-0'], ['name' => '3-0', 'header' => 'Home']],
            [['single_score' => '3-1'], ['name' => '3-1', 'header' => 'Home']],
            [['single_score' => '3-2'], ['name' => '3-2', 'header' => 'Home']],

            [['single_score' => '0-1'], ['name' => '1-0', 'header' => 'Away']],
            [['single_score' => '0-2'], ['name' => '2-0', 'header' => 'Away']],
            [['single_score' => '1-2'], ['name' => '2-1', 'header' => 'Away']],
            [['single_score' => '0-3'], ['name' => '3-0', 'header' => 'Away']],
            [['single_score' => '1-3'], ['name' => '3-1', 'header' => 'Away']],
            [['single_score' => '2-3'], ['name' => '3-2', 'header' => 'Away']],
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
            [['single_score' => '0-1'], ['name' => '0-0', 'header' => 'Draw']],
            [['single_score' => '2-1'], ['name' => '1-1', 'header' => 'Draw']],

            [['single_score' => '1-2'], ['name' => '1-0', 'header' => 'Home']],
            [['single_score' => '2-2'], ['name' => '2-0', 'header' => 'Home']],

            [['single_score' => '2-1'], ['name' => '1-0', 'header' => 'Away']],
            [['single_score' => '0-0'], ['name' => '2-0', 'header' => 'Away']],
            [['single_score' => '2-2'], ['name' => '2-1', 'header' => 'Away']],
            [['single_score' => '4-3'], ['name' => '3-0', 'header' => 'Away']],
        ];
    }
}
