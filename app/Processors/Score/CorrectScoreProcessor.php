<?php

namespace App\Processors\Score;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class CorrectScoreProcessor extends AbstractSelectionProcessor
{
    public function process(): string
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        if (in_array($this->selectionName(), $this->homeSelectionNames())) {
            if ($this->awayTeamWins() || $this->isDraw()) {
                return BetSelection::STATUS_LOST;
            }

            [$homeTeamSelectedScore, $awayTeamSelectedScore] = $this->selectedScores();

            return $this->areScoresCorrect(
                $homeTeamScore,
                $homeTeamSelectedScore,
                $awayTeamScore,
                $awayTeamSelectedScore
            ) ? BetSelection::STATUS_WON : BetSelection::STATUS_LOST;
        } elseif (in_array($this->selectionName(), $this->awaySelectionNames())) {
            if ($this->homeTeamWins() || $this->isDraw()) {
                return BetSelection::STATUS_LOST;
            }

            [$awayTeamSelectedScore, $homeTeamSelectedScore] = $this->selectedScores();

            return $this->areScoresCorrect(
                $homeTeamScore,
                $homeTeamSelectedScore,
                $awayTeamScore,
                $awayTeamSelectedScore
            ) ? BetSelection::STATUS_WON : BetSelection::STATUS_LOST;
        } elseif (in_array($this->selectionName(), $this->drawSelectionNames())) {
            if ($this->homeTeamWins() || $this->awayTeamWins()) {
                return BetSelection::STATUS_LOST;
            }

            [$homeTeamSelectedScore, $awayTeamSelectedScore] = $this->selectedScores();

            return $this->areScoresCorrect(
                $homeTeamScore,
                $homeTeamSelectedScore,
                $awayTeamScore,
                $awayTeamSelectedScore
            ) ? BetSelection::STATUS_WON : BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function selectedScores(): array
    {
        return explode('-', $this->selection->name);
    }

    protected function selectionName()
    {
        return $this->selection->header;
    }

    private function areScoresCorrect($homeTeamScore, $homeTeamSelectedScore, $awayTeamScore, $awayTeamSelectedScore)
    {
        return $homeTeamScore === $homeTeamSelectedScore && $awayTeamScore === $awayTeamSelectedScore;
    }

    private function homeTeamWins(): bool
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $homeTeamScore > $awayTeamScore;
    }

    private function awayTeamWins(): bool
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $homeTeamScore < $awayTeamScore;
    }

    private function isDraw(): bool
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $homeTeamScore === $awayTeamScore;
    }
}
