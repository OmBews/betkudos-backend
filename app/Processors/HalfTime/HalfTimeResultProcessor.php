<?php

namespace App\Processors\HalfTime;

use App\Processors\MatchResultProcessor;

class HalfTimeResultProcessor extends MatchResultProcessor
{
    protected function scores(): array
    {
        return $this->firstHalfScores();
    }
}
