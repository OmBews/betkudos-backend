<?php

namespace Tests\Unit\Processors\ThreeWay;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\ThreeWay\ResultProcessor;
use Tests\Unit\Processors\MatchResultProcessorTest;

class ThreeWayResultProcessorTest extends MatchResultProcessorTest
{
    protected static $selectionNameKey = 'header';

    protected function market(): Market
    {
        return Market::where('key', '3_way')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return ResultProcessor::factory($selection, $market);
    }
}
