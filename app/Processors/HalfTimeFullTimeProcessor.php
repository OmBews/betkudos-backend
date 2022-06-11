<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class HalfTimeFullTimeProcessor extends AbstractSelectionProcessor
{
    public function process(): string
    {
        [$halfTimeSelection, $fullTimeSelection] = $this->selections();

        [$homeTeamFirstHalfScore, $awayTeamFirstHalfScore] = $this->firstHalfScores();
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        $halfTimeStatus = $this->determineStatus($halfTimeSelection, $homeTeamFirstHalfScore, $awayTeamFirstHalfScore);
        $fullTimeStatus = $this->determineStatus($fullTimeSelection, $homeTeamScore, $awayTeamScore);

        if ($halfTimeStatus === BetSelection::STATUS_WON && $fullTimeStatus === BetSelection::STATUS_WON) {
            return BetSelection::STATUS_WON;
        } elseif (in_array(BetSelection::STATUS_LOST, [$halfTimeStatus, $fullTimeStatus])) {
            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function selections(): array
    {
        return explode(' - ', $this->selection->name);
    }

    private function determineStatus($selection, $homeTeamScore, $awayTeamScore): string
    {
        if (in_array($selection, $this->drawSelectionNames())) {
            if ($homeTeamScore === $awayTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif (in_array($selection, $this->homeSelectionNames())) {
            if ($homeTeamScore > $awayTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif (in_array($selection, $this->awaySelectionNames())) {
            if ($awayTeamScore > $homeTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }
}
