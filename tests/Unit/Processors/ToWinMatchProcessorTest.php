<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\ToWinMatchProcessor;
use PHPUnit\Framework\TestCase;

class ToWinMatchProcessorTest extends ProcessorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\TennisMarketsSeeder::class);
    }

    protected function market(): Market
    {
        return Market::where('key', 'to_win_match')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return ToWinMatchProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider selectionWonTheMatch
     */
    public function testWillWonWhenTheSelectionWonMoreSets(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function selectionWonTheMatch(): array
    {
        return [
            [['single_score' => '6-4,6-3'], [static::$selectionNameKey => 'Home']],
            [['single_score' => '6-2,6-4'], [static::$selectionNameKey => 'Home']],
            [['single_score' => '6-3,6-4,6-4'], [static::$selectionNameKey => 'Home']],
            [['single_score' => '6-2,6-4,6-2'], [static::$selectionNameKey => 'Home']],

            [['single_score' => '4-6,3-6'], [static::$selectionNameKey => 'Away']],
            [['single_score' => '2-6,4-6'], [static::$selectionNameKey => 'Away']],
            [['single_score' => '3-6,4-6,4-6'], [static::$selectionNameKey => 'Away']],
            [['single_score' => '2-6,4-6,2-6'], [static::$selectionNameKey => 'Away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider selectionLoseTheMatch
     */
    public function testWillLoseWhenTheSelectionLoseMoreSets(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function selectionLoseTheMatch(): array
    {
        return [
            [['single_score' => '4-6,3-6'], [static::$selectionNameKey => 'Home']],
            [['single_score' => '2-6,4-6'], [static::$selectionNameKey => 'Home']],
            [['single_score' => '3-6,4-6,4-6'], [static::$selectionNameKey => 'Home']],
            [['single_score' => '2-6,4-6,2-6'], [static::$selectionNameKey => 'Home']],

            [['single_score' => '6-4,6-3'], [static::$selectionNameKey => 'Away']],
            [['single_score' => '6-2,6-4'], [static::$selectionNameKey => 'Away']],
            [['single_score' => '6-3,6-4,6-4'], [static::$selectionNameKey => 'Away']],
            [['single_score' => '6-2,6-4,6-2'], [static::$selectionNameKey => 'Away']],
        ];
    }
}
