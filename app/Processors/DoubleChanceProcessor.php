<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class DoubleChanceProcessor extends AbstractSelectionProcessor
{
    public function process(): string
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        $homeTeamName = $this->selection->match->home->name;
        $awayTeamName = $this->selection->match->away->name;
        $selectionName = $this->selection->name;

        $homeTeamOrDrawSelections = ["$homeTeamName or Draw", "$homeTeamName or X", '1X'];
        $drawOrAwayTeamSelections = ["Draw or $awayTeamName", "X or $awayTeamName", '2X'];
        $homeTemOrAwayTeam = ["$homeTeamName or $awayTeamName", '12'];

        if (in_array($selectionName, $homeTeamOrDrawSelections)) {
            if ($homeTeamScore > $awayTeamScore || $homeTeamScore === $awayTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif (in_array($selectionName, $drawOrAwayTeamSelections)) {
            if ($homeTeamScore === $awayTeamScore || $awayTeamScore > $homeTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif (in_array($selectionName, $homeTemOrAwayTeam)) {
            if ($homeTeamScore > $awayTeamScore || $awayTeamScore > $homeTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }
}
