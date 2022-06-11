<?php

namespace App\Processors\Goals;

class FirstHalfGoalsOverUnderProcessor extends GoalsOverUnderProcessor
{
    protected function scores(): array
    {
        return $this->firstHalfScores();
    }
}
