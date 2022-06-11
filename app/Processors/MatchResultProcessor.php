<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Sports\Sport;
use Illuminate\Support\Arr;

class MatchResultProcessor extends AbstractSelectionProcessor
{
    /**
     * @inheritDoc
     */
    public function process(): string
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        $selectionName = $this->selectionName();

        if ($this->market->sport->isCricket()) {
            $homeTeamScore = Arr::first(explode('/', $homeTeamScore));
            $awayTeamScore = Arr::first(explode('/', $awayTeamScore));
        }

        if (!is_numeric($homeTeamScore) || !is_numeric($awayTeamScore)) {
            return BetSelection::STATUS_OPEN;
        }

        if (in_array($selectionName, $this->drawSelectionNames())) {
            if ($homeTeamScore === $awayTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif (in_array($selectionName, $this->homeSelectionNames())) {
            if ($homeTeamScore > $awayTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif (in_array($selectionName, $this->awaySelectionNames())) {
            if ($homeTeamScore < $awayTeamScore) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }
}
