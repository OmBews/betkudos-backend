<?php

namespace App\Processors\MatchMarkets;

use App\Contracts\Processors\HybridMarketSelectionProcessor;
use App\Processors\ThreeWay\ResultProcessor;

class MatchMarketsProcessor extends HybridMarketSelectionProcessor
{
    protected function processors(): array
    {
        return [
            'Winner' => ResultProcessor::class
        ];
    }
}
