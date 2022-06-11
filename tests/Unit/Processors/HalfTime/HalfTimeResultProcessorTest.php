<?php

namespace Tests\Unit\Processors\HalfTime;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\HalfTime\HalfTimeResultProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class HalfTimeResultProcessorTest extends ProcessorTestCase
{
    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return HalfTimeResultProcessor::factory($selection, $market);
    }

    protected function market(): Market
    {
        return Market::where('key', 'half_time_result')->first();
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider halfTimeDrawWon
     */
    public function testWillWonWhenTheSelectionIsDrawAndTheResultIsDrawByTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function halfTimeDrawWon()
    {
        return [
            [
                ['single_score' => '1-2', 'scores' => '{"2":{"home":"0","away":"1"},"1":{"home":"1","away":"1"}}'],
                ['name' => 'Draw']
            ],
            [
                ['single_score' => '3-2', 'scores' => '{"2":{"home":"2","away":"1"},"1":{"home":"1","away":"1"}}'],
                ['name' => 'draw']
            ],
            [
                ['single_score' => '1-0', 'scores' => '{"2":{"home":"1","away":"0"},"1":{"home":"0","away":"0"}}'],
                ['name' => 'x']
            ],
            [
                ['single_score' => '2-0', 'scores' => '{"2":{"home":"2","away":"0"},"1":{"home":"0","away":"0"}}'],
                ['name' => 'X']
            ]
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider halfTimeDrawLoss
     */
    public function testWillLossWhenTheSelectionIsDrawButTheResultIsDifferentByTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function halfTimeDrawLoss()
    {
        return [
            [
                ['single_score' => '2-2', 'scores' => '{"2":{"home":"0","away":"1"},"1":{"home":"2","away":"1"}}'],
                ['name' => 'Draw']
            ],
            [
                ['single_score' => '2-2', 'scores' => '{"2":{"home":"2","away":"1"},"1":{"home":"0","away":"1"}}'],
                ['name' => 'draw']
            ],
            [
                ['single_score' => '2-0', 'scores' => '{"2":{"home":"1","away":"0"},"1":{"home":"1","away":"0"}}'],
                ['name' => 'x']
            ],
            [
                ['single_score' => '2-1', 'scores' => '{"2":{"home":"2","away":"0"},"1":{"home":"0","away":"1"}}'],
                ['name' => 'X']
            ]
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamWonHalfTime
     */
    public function testWillWonWhenTheSelectionAndResultIsHomeTeamByTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeTeamWonHalfTime()
    {
        return [
            [
                ['single_score' => '2-2', 'scores' => '{"2":{"home":"0","away":"1"},"1":{"home":"2","away":"1"}}'],
                ['name' => '1']
            ],
            [
                ['single_score' => '3-1', 'scores' => '{"2":{"home":"2","away":"1"},"1":{"home":"1","away":"0"}}'],
                ['name' => 'Home']
            ],
            [
                ['single_score' => '3-1', 'scores' => '{"2":{"home":"1","away":"0"},"1":{"home":"2","away":"1"}}'],
                ['name' => 'home']
            ]
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamLossHalfTime
     */
    public function testWillLossWhenTheSelectionIsHomeButTheResultIsDifferentByTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamLossHalfTime()
    {
        return [
            [
                ['single_score' => '1-3', 'scores' => '{"2":{"home":"0","away":"1"},"1":{"home":"1","away":"2"}}'],
                ['name' => '1']
            ],
            [
                ['single_score' => '2-2', 'scores' => '{"2":{"home":"2","away":"1"},"1":{"home":"0","away":"1"}}'],
                ['name' => 'Home']
            ],
            [
                ['single_score' => '2-2', 'scores' => '{"2":{"home":"1","away":"0"},"1":{"home":"1","away":"2"}}'],
                ['name' => 'home']
            ]
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamWonHalfTime
     */
    public function testWillWonWhenTheSelectionAndResultIsAwayTeamByTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayTeamWonHalfTime()
    {
        return [
            [
                ['single_score' => '1-3', 'scores' => '{"2":{"home":"0","away":"1"},"1":{"home":"1","away":"2"}}'],
                ['name' => '2']
            ],
            [
                ['single_score' => '2-2', 'scores' => '{"2":{"home":"2","away":"1"},"1":{"home":"0","away":"1"}}'],
                ['name' => 'Away']
            ],
            [
                ['single_score' => '3-3', 'scores' => '{"2":{"home":"1","away":"0"},"1":{"home":"2","away":"3"}}'],
                ['name' => 'away']
            ]
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamLossHalfTime
     */
    public function testWillLossWhenTheSelectionIsAwayButTheResultIsDifferentByTheHalfTime(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamLossHalfTime()
    {
        return [
            [
                ['single_score' => '1-2', 'scores' => '{"2":{"home":"0","away":"1"},"1":{"home":"1","away":"1"}}'],
                ['name' => '2']
            ],
            [
                ['single_score' => '4-2', 'scores' => '{"2":{"home":"2","away":"1"},"1":{"home":"2","away":"1"}}'],
                ['name' => 'Away']
            ],
            [
                ['single_score' => '2-0', 'scores' => '{"2":{"home":"1","away":"0"},"1":{"home":"1","away":"0"}}'],
                ['name' => 'away']
            ]
        ];
    }
}
