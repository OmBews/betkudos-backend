<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class GoalscorersProcessor extends AbstractSelectionProcessor
{
    public function process(): string
    {
        if ($this->willScoreFirstGoal()) {
            if ($this->scoredFirstGoal()) {
                return BetSelection::STATUS_WON;
            }
            return BetSelection::STATUS_LOST;
        } elseif ($this->willScoreLastGoal()) {
            if ($this->scoredLastGoal()) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif ($this->willScoreAnytime()) {
            if ($this->scoredAnyGoals()) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function when()
    {
        return $this->selection->header;
    }

    protected function willScoreFirstGoal(): bool
    {
        return strtolower($this->when()) === 'first';
    }

    protected function willScoreLastGoal(): bool
    {
        return strtolower($this->when()) === 'last';
    }

    protected function willScoreAnytime(): bool
    {
        return strtolower($this->when()) === 'anytime';
    }

    private function scoredFirstGoal(): bool
    {
        foreach ($this->events() as $event) {
            if (str_contains($event->text, "1st Goal - {$this->selectionName()}")) {
                return true;
            }
        }

        return false;
    }

    private function scoredLastGoal()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();
        $lastGoal = $this->addOrdinalNumberSuffix($homeTeamScore + $awayTeamScore);

        foreach ($this->events() as $event) {
            if (str_contains($event->text, "$lastGoal Goal - {$this->selectionName()}")) {
                return true;
            }
        }

        return false;
    }

    private function scoredAnyGoals()
    {
        foreach ($this->events() as $event) {
            if (str_contains($event->text, "Goal - {$this->selectionName()}")) {
                return true;
            }
        }

        return false;
    }

    private function addOrdinalNumberSuffix($num)
    {
        if (!in_array(($num % 100), array(11,12,13))) {
            switch ($num % 10) {
              // Handle 1st, 2nd, 3rd
                case 1:
                    return $num . 'st';
                case 2:
                    return $num . 'nd';
                case 3:
                    return $num . 'rd';
            }
        }

        return $num . 'th';
    }

    protected function events()
    {
        return json_decode($this->selection->match->stats->events);
    }
}
