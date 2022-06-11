<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\TotalGoalsProcessor;

class TotalGoalsProcessorTest extends ProcessorTestCase
{
    protected static $selectionNameKey = 'header';

    protected function market(): Market
    {
        return Market::where('key', 'game_lines')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return TotalGoalsProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underWins
     */
    public function testWillWinWhenTheSelectionAndResultIsUnder(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function underWins()
    {
        return [
            [
                ['single_score' => '49-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'U 50.00'],
            ],
            [
                ['single_score' => '20-45'], [self::$selectionNameKey => 'Away', 'handicap' => 'U 50.00'],
            ],
            [
                ['single_score' => '49-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'Under 50.00'],
            ],
            [
                ['single_score' => '20-45'], [self::$selectionNameKey => 'Away', 'handicap' => 'Under 50.00'],
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overWins
     */
    public function testWillWinWhenTheSelectionAndResultIsOver(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function overWins()
    {
        return [
            [
                ['single_score' => '51-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'O 50.00'],
            ],
            [
                ['single_score' => '20-60'], [self::$selectionNameKey => 'Away', 'handicap' => 'O 50.00'],
            ],
            [
                ['single_score' => '55-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'Over 50.00'],
            ],
            [
                ['single_score' => '20-70'], [self::$selectionNameKey => 'Away', 'handicap' => 'Over 50.00'],
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underLose
     */
    public function testWillLoseWhenTheSelectionIsUnderAndResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function underLose()
    {
        return [
            [
                ['single_score' => '51-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'U 50.00'],
            ],
            [
                ['single_score' => '20-55'], [self::$selectionNameKey => 'Away', 'handicap' => 'U 50.00'],
            ],
            [
                ['single_score' => '51-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'Under 50.00'],
            ],
            [
                ['single_score' => '20-55'], [self::$selectionNameKey => 'Away', 'handicap' => 'Under 50.00'],
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overLose
     */
    public function testWillLoseWhenTheSelectionIsOverAndResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function overLose()
    {
        return [
            [
                ['single_score' => '45-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'O 50.00'],
            ],
            [
                ['single_score' => '20-30'], [self::$selectionNameKey => 'Away', 'handicap' => 'O 50.00'],
            ],
            [
                ['single_score' => '45-20'], [self::$selectionNameKey => 'Home', 'handicap' => 'Over 50.00'],
            ],
            [
                ['single_score' => '20-30'], [self::$selectionNameKey => 'Away', 'handicap' => 'Over 50.00'],
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider refundWhenHandicapIsEqualsToSelection
     */
    public function testWillRefundWhenTheResultIsEqualsToTheHandicap(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_VOID);
    }

    public function refundWhenHandicapIsEqualsToSelection()
    {
        return [
            [
                ['single_score' => '50-40'], [self::$selectionNameKey => 'Home', 'handicap' => 'O 50.00'],
            ],
            [
                ['single_score' => '50-40'], [self::$selectionNameKey => 'Home', 'handicap' => 'Over 50.00'],
            ],
            [
                ['single_score' => '40-50'], [self::$selectionNameKey => 'Away', 'handicap' => 'U 50.00'],
            ],
            [
                ['single_score' => '40-50'], [self::$selectionNameKey => 'Away', 'handicap' => 'Under 50.00'],
            ],
        ];
    }
}
