<?php

namespace Tests\Unit\Processors\Goals;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\Goals\FirstHalfGoalsOverUnderProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

class FirstHalfGoalsOverUnderProcessorTest extends ProcessorTestCase
{

    protected function market(): Market
    {
        return Market::where('key', 'first_half_goals')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return FirstHalfGoalsOverUnderProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underWonFirstHalf
     */
    public function testWillWonWhenTheSelectionAndTheResultIsUnderByTheFirstHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function underWonFirstHalf()
    {
        return [
            // Under 0.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Under', 'name' => '0.5']
            ],

            // Under 1.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Under', 'name' => '1.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Under', 'name' => '1.5']
            ],

            // Under 2.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Under', 'name' => '2.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Under', 'name' => '2.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Under', 'name' => '2.5']
            ],

            // Under 3.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Under', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Under', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Under', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Under', 'name' => '3.5']
            ],

            // Under 4.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Under', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Under', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Under', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Under', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Under', 'name' => '4.5']
            ],

            // Under 5.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"5","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overWonFirstHalf
     */
    public function testWillWonWhenTheSelectionAndTheResultIsOverByTheFirstHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function overWonFirstHalf()
    {
        return [
            // Over 0.5
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Over', 'name' => '0.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Over', 'name' => '0.5']
            ],

            // Over 1.5
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Over', 'name' => '1.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Over', 'name' => '1.5']
            ],

            // Over 2.5
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Over', 'name' => '2.5']
            ],
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Over', 'name' => '2.5']
            ],

            // Over 3.5
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Over', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"5","away":"0"}}'],
                ['header' => 'Over', 'name' => '3.5']
            ],

            // Over 4.5
            [
                ['scores' => '{"1":{"home":"5","away":"0"}}'],
                ['header' => 'Over', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"6","away":"0"}}'],
                ['header' => 'Over', 'name' => '4.5']
            ],

            // Over 5.5
            [
                ['scores' => '{"1":{"home":"6","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"7","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider overLossFirstHalf
     */
    public function testWillLossWhenTheSelectionIsOverButTheResultIsUnderByTheFirstHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function overLossFirstHalf()
    {
        return [
            // Over 0.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Over', 'name' => '0.5']
            ],

            // Over 1.5
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Over', 'name' => '1.5']
            ],

            // Over 2.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Over', 'name' => '2.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Over', 'name' => '2.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Over', 'name' => '2.5']
            ],

            // Over 3.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Over', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Over', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Over', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Over', 'name' => '3.5']
            ],

            // Over 4.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Over', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Over', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Over', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Over', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Over', 'name' => '4.5']
            ],

            // Over 5.5
            [
                ['scores' => '{"1":{"home":"0","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"5","away":"0"}}'],
                ['header' => 'Over', 'name' => '5.5']
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider underLoss
     */
    public function testWillLossWhenTheSelectionIsUnderButTheResultIsOverByTheFirstHalf(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function underLoss()
    {
        return [
            // Under 0.5
            [
                ['scores' => '{"1":{"home":"1","away":"0"}}'],
                ['header' => 'Under', 'name' => '0.5']
            ],

            // Under 1.5
            [
                ['scores' => '{"1":{"home":"2","away":"0"}}'],
                ['header' => 'Under', 'name' => '1.5']
            ],
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Under', 'name' => '1.5']
            ],

            // Under 2.5
            [
                ['scores' => '{"1":{"home":"3","away":"0"}}'],
                ['header' => 'Under', 'name' => '2.5']
            ],
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Under', 'name' => '2.5']
            ],

            // Under 3.5
            [
                ['scores' => '{"1":{"home":"4","away":"0"}}'],
                ['header' => 'Under', 'name' => '3.5']
            ],
            [
                ['scores' => '{"1":{"home":"5","away":"0"}}'],
                ['header' => 'Under', 'name' => '3.5']
            ],

            // Under 4.5
            [
                ['scores' => '{"1":{"home":"5","away":"0"}}'],
                ['header' => 'Under', 'name' => '4.5']
            ],
            [
                ['scores' => '{"1":{"home":"6","away":"0"}}'],
                ['header' => 'Under', 'name' => '4.5']
            ],

            // Under 5.5
            [
                ['scores' => '{"1":{"home":"6","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
            [
                ['scores' => '{"1":{"home":"7","away":"0"}}'],
                ['header' => 'Under', 'name' => '5.5']
            ],
        ];
    }
}
