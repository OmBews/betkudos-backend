<?php

namespace App\Processors\Score;

class BothTeamsToScoreInFirstHalfProcessor extends BothTeamsToScoreProcessor
{
    protected function scores(): array
    {
        return $this->firstHalfScores();
    }
}
