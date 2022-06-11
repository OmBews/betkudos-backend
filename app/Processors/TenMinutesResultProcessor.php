<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class TenMinutesResultProcessor extends AbstractSelectionProcessor
{
    private const TEN_MINUTE_IDENTIFIER = ' Goals 00:00 - 09:59';

    public function process(): string
    {
        if ($this->isSelectionDraw()) {
            if ($this->hasEndedInDraw()) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif ($this->isSelectionHomeTeam()) {
            if ($this->homeTeamWon()) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        } elseif ($this->isSelectionAwayTeam()) {
            if ($this->awayTeamWon()) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function scores(): array
    {
        $scores = [];
        $events = json_decode($this->selection->match->stats->events);

        foreach ($events as $event) {
            if (str_contains($event->text, self::TEN_MINUTE_IDENTIFIER)) {
                $scoresText = str_replace(self::TEN_MINUTE_IDENTIFIER, "", $event->text);

                $scores = explode(':', $scoresText);

                break;
            }
        }

        return $scores;
    }
}
