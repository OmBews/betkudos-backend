<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use Illuminate\Support\Str;

class TotalGoalsProcessor extends AbstractSelectionProcessor
{
    private const UNDER_IDENTIFIERS = ['U', 'Under'];
    private const OVER_IDENTIFIERS = ['O', 'Over'];

    public function process(): string
    {
        $teamGoals = $this->isSelectionHomeTeam() ? $this->homeTeamGoals() : $this->awayTeamGoals();

        if ($this->isSelectionHomeTeam() || $this->isSelectionAwayTeam()) {
            if ((int) $teamGoals === $this->limit()) {
                return BetSelection::STATUS_VOID;
            } elseif ($this->isOver()) {
                if ($teamGoals > $this->limit()) {
                    return  BetSelection::STATUS_WON;
                }

                return  BetSelection::STATUS_LOST;
            } elseif ($this->isUnder()) {
                if ($teamGoals < $this->limit()) {
                    return  BetSelection::STATUS_WON;
                }

                return  BetSelection::STATUS_LOST;
            }
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function homeTeamGoals()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $homeTeamScore;
    }

    protected function awayTeamGoals()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $awayTeamScore;
    }

    protected function limit(): int
    {
        $handicap = $this->handicap();

        if ($this->isUnder()) {
            foreach (self::UNDER_IDENTIFIERS as $identifier) {
                $handicap = str_replace("$identifier ", '', $handicap);
            }
        } else {
            foreach (self::OVER_IDENTIFIERS as $identifier) {
                $handicap = str_replace("$identifier ", '', $handicap);
            }
        }

        return $handicap;
    }

    protected function handicap()
    {
        return $this->selection->handicap;
    }

    protected function isOver(): bool
    {
        return Str::of($this->handicap())->contains(self::OVER_IDENTIFIERS);
    }

    protected function isUnder(): bool
    {
        return Str::of($this->handicap())->contains(self::UNDER_IDENTIFIERS);
    }

    protected function selectionName()
    {
        return $this->selection->header;
    }
}
