<?php

namespace App\Processors\GameBetting2Way;

use App\Contracts\Processors\HybridMarketSelectionProcessor;
use App\Processors\ThreeWay\ResultProcessor;

class GameBetting2WayProcessor extends HybridMarketSelectionProcessor
{
    protected function processors(): array
    {
        return [
            'To Win' => ResultProcessor::class
        ];
    }
}
