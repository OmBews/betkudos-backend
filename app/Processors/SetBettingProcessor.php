<?php

namespace App\Processors;

use App\Models\Bets\Selections\BetSelection;

class SetBettingProcessor extends ToWinMatchProcessor
{
    public function process(): string
    {
        [$homeTeamScore, $awayTeamScore] = $this->setsResult();

        $homeTeamResult = "$homeTeamScore-$awayTeamScore";
        $awayTeamResult = "$awayTeamScore-$homeTeamScore";

        if ($this->isSelectionHomeTeam()) {
            if ($this->expectedSetResult() === $homeTeamResult) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif ($this->isSelectionAwayTeam()) {
            if ($this->expectedSetResult() === $awayTeamResult) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function expectedSetResult(): string
    {
        return $this->selection->name;
    }

    protected function selectionName(): string
    {
        return $this->selection->header;
    }
}
