<?php

namespace App\Processors\Goals;

class FirstHalfGoalsOddEvenProcessor extends GoalsOddEvenProcessor
{
    protected function scores(): array
    {
        return $this->firstHalfScores();
    }
}
