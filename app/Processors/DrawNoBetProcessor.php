<?php

namespace App\Processors;

use App\Models\Bets\Selections\BetSelection;

class DrawNoBetProcessor extends MatchResultProcessor
{
    public function process(): string
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        if ($homeTeamScore === $awayTeamScore) {
            return BetSelection::STATUS_VOID;
        }

        $status = parent::process();

        if (in_array($status, [BetSelection::STATUS_WON, BetSelection::STATUS_LOST])) {
            return $status;
        }

        return BetSelection::STATUS_OPEN;
    }
}
