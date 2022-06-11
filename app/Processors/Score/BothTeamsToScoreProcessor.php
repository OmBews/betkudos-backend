<?php

namespace App\Processors\Score;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class BothTeamsToScoreProcessor extends AbstractSelectionProcessor
{
    private const BOTH_TO_SCORE_IDENTIFIER = 'Yes';
    private const BOTH_TO_DO_NOT_SCORE_IDENTIFIER = 'No';

    public function process(): string
    {
        [$homeTeamGoals, $awayTeamGoals] = $this->scores();

        $selectionName = strtolower($this->selection->name);

        if ($selectionName === strtolower(self::BOTH_TO_SCORE_IDENTIFIER)) {
            if ($homeTeamGoals > 0 && $awayTeamGoals > 0) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif ($selectionName === strtolower(self::BOTH_TO_DO_NOT_SCORE_IDENTIFIER)) {
            if ($homeTeamGoals == 0 || $awayTeamGoals == 0) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }
}
