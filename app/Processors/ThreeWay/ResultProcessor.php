<?php

namespace App\Processors\ThreeWay;

use App\Processors\MatchResultProcessor;

class ResultProcessor extends MatchResultProcessor
{
    protected function selectionName(): string
    {
        return $this->selection->header;
    }
}
