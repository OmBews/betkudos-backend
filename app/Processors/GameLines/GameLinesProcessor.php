<?php

namespace App\Processors\GameLines;

use App\Contracts\Processors\HybridMarketSelectionProcessor;
use App\Processors\ThreeWay\ResultProcessor;
use App\Processors\TotalGoalsProcessor;

class GameLinesProcessor extends HybridMarketSelectionProcessor
{
    protected function processors(): array
    {
        return [
            'Spread' => SpreadProcessor::class,
            'Handicap' => SpreadProcessor::class,
            'Total' => TotalGoalsProcessor::class,
            'Money Line' => ResultProcessor::class
        ];
    }
}
