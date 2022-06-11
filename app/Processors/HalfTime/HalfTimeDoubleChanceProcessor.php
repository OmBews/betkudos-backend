<?php

namespace App\Processors\HalfTime;

use App\Processors\DoubleChanceProcessor;

class HalfTimeDoubleChanceProcessor extends DoubleChanceProcessor
{
    protected function scores(): array
    {
        return $this->firstHalfScores();
    }
}
