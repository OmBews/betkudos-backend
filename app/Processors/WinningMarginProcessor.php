<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class WinningMarginProcessor extends AbstractSelectionProcessor
{
    public function process(): string
    {
        $selection = $this->selection;

        /**
         * The bettor placed a bet in a Score Draw or in a event with No Goals
         **/
        if ($this->selectionName() === null) {
            if ($selection->name === "1" && $this->isScoreDraw()) {
                return BetSelection::STATUS_WON;
            } elseif ($selection->name === "2" && $this->thereIsNoGoals()) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        [$homeTeamScore, $awayTeamScore] = $this->scores();
        $margin = $this->selection->name;

        if ($this->selectionName() === "1") {
            if ($homeTeamScore > $awayTeamScore) {
                $finalMargin = $homeTeamScore - $awayTeamScore;

                return $this->winningMarginBetResult($margin, $finalMargin);
            }

            return BetSelection::STATUS_LOST;
        } elseif ($this->selectionName() === "2") {
            if ($awayTeamScore > $homeTeamScore) {
                $finalMargin = $awayTeamScore - $homeTeamScore;

                return $this->winningMarginBetResult($margin, $finalMargin);
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    private function isScoreDraw()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        if ($homeTeamScore === $awayTeamScore && ($homeTeamScore > 0 && $awayTeamScore > 0)) {
            return true;
        }

        return false;
    }

    private function thereIsNoGoals(): bool
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return (int) $homeTeamScore === 0 && (int) $awayTeamScore === 0;
    }

    private function winningMarginBetResult($margin, $finalMargin): string
    {
        /**
         * If the user placed a bet for example on 4 or + goals
         **/
        if (str_contains($margin, '+')) {
            $margin = str_replace('+', '', $margin);

            if ($finalMargin >= $margin) {
                return BetSelection::STATUS_WON;
            }
        }

        /**
         * When the user placed a bet for example on exactly 3 goals
         * where the final margin and the margin is equals to 3.
         **/
        if ((int) $finalMargin === (int) $margin) {
            return BetSelection::STATUS_WON;
        }

        return BetSelection::STATUS_LOST;
    }

    protected function selectionName(): ?string
    {
        return $this->selection->header;
    }
}
