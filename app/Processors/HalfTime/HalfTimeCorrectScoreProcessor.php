<?php

namespace App\Processors\HalfTime;

use App\Processors\Score\CorrectScoreProcessor;

class HalfTimeCorrectScoreProcessor extends CorrectScoreProcessor
{
    protected function scores(): array
    {
        return $this->firstHalfScores();
    }
}
