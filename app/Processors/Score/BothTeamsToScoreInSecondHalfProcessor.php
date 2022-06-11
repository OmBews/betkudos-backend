<?php

namespace App\Processors\Score;

class BothTeamsToScoreInSecondHalfProcessor extends BothTeamsToScoreProcessor
{
    protected function scores(): array
    {
        return $this->secondHalfScores();
    }
}
