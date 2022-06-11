<?php

namespace App\Processors\ThreeWay;

use App\Contracts\Processors\HybridMarketSelectionProcessor;

class ThreeWayProcessor extends HybridMarketSelectionProcessor
{
    protected function processors(): array
    {
        return [
            'Result' => ResultProcessor::class,
            'Money Line' => ResultProcessor::class,
        ];
    }
}
