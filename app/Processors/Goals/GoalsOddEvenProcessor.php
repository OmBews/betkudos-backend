<?php

namespace App\Processors\Goals;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class GoalsOddEvenProcessor extends AbstractSelectionProcessor
{

    public function process(): string
    {
        $result = $this->goals() % 2;
        $side = $this->selection->name;

        if ($side === "Even") {
            if ($result === 0) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif ($side === "Odd") {
            if ($result === 1) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function goals()
    {
        [$homeTeamGoals, $awayTeamGoals] = $this->scores();

        return $homeTeamGoals + $awayTeamGoals;
    }
}
