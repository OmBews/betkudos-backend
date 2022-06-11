<?php

namespace App\Processors\GameLines;

use App\Processors\AsianLines\AsianHandicapProcessor;

class SpreadProcessor extends AsianHandicapProcessor
{
    protected function rawHandicap()
    {
        return $this->selection->handicap;
    }
}
