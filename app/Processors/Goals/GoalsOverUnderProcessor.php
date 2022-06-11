<?php

namespace App\Processors\Goals;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;

class GoalsOverUnderProcessor extends AbstractSelectionProcessor
{
    private const OVER_DIFFERENCE = 0.5;

    private const UNDER_DIFFERENCE = 0.5;

    private const OVER_IDENTIFIER = 'Over';

    private const UNDER_IDENTIFIER = 'Under';

    public function process(): string
    {
        $header = $this->selection->header;

        if (strtolower($header) === strtolower(self::OVER_IDENTIFIER)) {
            if ($this->goals() >= $this->limit() + self::OVER_DIFFERENCE) {
                return BetSelection::STATUS_WON;
            }

            return  BetSelection::STATUS_LOST;
        } elseif (strtolower($header) === strtolower(self::UNDER_IDENTIFIER)) {
            if ($this->goals() <= $this->limit() - self::UNDER_DIFFERENCE) {
                return BetSelection::STATUS_WON;
            }

            return  BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    protected function goals()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $homeTeamScore + $awayTeamScore;
    }

    protected function limit()
    {
        return $this->selection->name;
    }
}
