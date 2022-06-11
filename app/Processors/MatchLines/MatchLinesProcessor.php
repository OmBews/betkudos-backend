<?php

namespace App\Processors\MatchLines;

use App\Contracts\Processors\HybridMarketSelectionProcessor;
use App\Processors\GameLines\SpreadProcessor;
use App\Processors\ThreeWay\ResultProcessor;
use App\Processors\TotalGoalsProcessor;

class MatchLinesProcessor extends HybridMarketSelectionProcessor
{
    protected function processors(): array
    {
        return [
            'Winner' => ResultProcessor::class,
            'To Win' => ResultProcessor::class,
            'Match Handicap' => SpreadProcessor::class,
            'Total Maps' => TotalGoalsProcessor::class
        ];
    }
}
