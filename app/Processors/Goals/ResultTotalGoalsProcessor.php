<?php

namespace App\Processors\Goals;

use App\Models\Bets\Selections\BetSelection;
use App\Processors\MatchResultProcessor;

class ResultTotalGoalsProcessor extends GoalsOverUnderProcessor
{
    public function process(): string
    {
        $overUnderStatus = parent::process();

        if ($overUnderStatus === BetSelection::STATUS_OPEN) {
            return BetSelection::STATUS_OPEN;
        }

        $fullTimeResultStatus = MatchResultProcessor::factory($this->selection, $this->market)->process();

        if ($fullTimeResultStatus === BetSelection::STATUS_WON && $overUnderStatus === BetSelection::STATUS_WON) {
            return BetSelection::STATUS_WON;
        }

        return BetSelection::STATUS_LOST;
    }

    protected function limit()
    {
        return $this->selection->handicap;
    }
}
