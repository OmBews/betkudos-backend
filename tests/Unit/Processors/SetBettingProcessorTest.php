<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\SetBettingProcessor;
use PHPUnit\Framework\TestCase;

class SetBettingProcessorTest extends ProcessorTestCase
{
    protected static $selectionNameKey = 'header';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\TennisMarketsSeeder::class);
    }

    protected function market(): Market
    {
        return Market::where('key', 'set_betting')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return SetBettingProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider selectionWon
     */
    public function testWonWhenTheResultAndTheSelectionAreTheSame(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function selectionWon(): array
    {
        return [
            [
                ['single_score' => '6-2,6-4'], [self::$selectionNameKey => 'Home', 'name' =>  '2-0']
            ],
            [
                ['single_score' => '6-2,4-6,6-2'], [self::$selectionNameKey => 'Home', 'name' =>  '2-1']
            ],

            [
                ['single_score' => '2-6,4-6'], [self::$selectionNameKey => 'Away', 'name' =>  '2-0']
            ],
            [
                ['single_score' => '2-6,6-4,2-6'], [self::$selectionNameKey => 'Away', 'name' =>  '2-1']
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider selectionLose
     */
    public function testWillLoseWhenTheResultAndTheSelectionAreDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function selectionLose(): array
    {
        return [
            [
                ['single_score' => '2-6,4-6'], [self::$selectionNameKey => 'Home', 'name' =>  '2-0']
            ],
            [
                ['single_score' => '2-6,6-4,2-6'], [self::$selectionNameKey => 'Home', 'name' =>  '2-1']
            ],

            [
                ['single_score' => '6-2,6-4'], [self::$selectionNameKey => 'Away', 'name' =>  '2-0']
            ],
            [
                ['single_score' => '6-2,4-6,6-2'], [self::$selectionNameKey => 'Away', 'name' =>  '2-1']
            ],
        ];
    }
}
