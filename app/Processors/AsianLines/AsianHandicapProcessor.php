<?php

namespace App\Processors\AsianLines;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Contracts\Processors\AsianLines\AsianHandicapProcessorInterface;
use App\Models\Bets\Selections\BetSelection;
use Illuminate\Support\Facades\Log;

class AsianHandicapProcessor extends AbstractSelectionProcessor implements AsianHandicapProcessorInterface
{
    private const HANDICAP_REMAINDER_DIVISOR = 1;

    protected const HANDICAP_DELIMITER = ', ';

    public function process(): string
    {
        $simpleHandicap = $this->parseHandicapSelection();

        try {
            if ($this->isFullGoalLine($simpleHandicap) && (int) $simpleHandicap === 0) {
                return $this->processDrawNoBet();
            } elseif ($this->isFullGoalLine($simpleHandicap)) {
                return (new FullGoalAsianLine($this, $simpleHandicap))->status();
            } elseif ($this->isHalfGoalLine($simpleHandicap)) {
                return (new HalfGoalAsianLine($this, $simpleHandicap))->status();
            } elseif ($this->isQuarterGoalLine($simpleHandicap)) {
                return (new QuarterGoalAsianLine($this, $simpleHandicap))->status();
            }
        } catch (\Throwable $exception) {
            Log::emergency($exception->getMessage());
        }

        return BetSelection::STATUS_OPEN;
    }

    private function processDrawNoBet(): string
    {
        if ($this->hasEndedInDraw()) {
            return BetSelection::STATUS_VOID;
        }

        if ($this->isSelectionHomeTeam()) {
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

    protected function rawHandicap()
    {
        return $this->selection->name;
    }

    protected function selectionName()
    {
        return $this->selection->header;
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getSelectionResult(): string
    {
        return parent::getSelectionResult();
    }

    public function scores(): array
    {
        return parent::scores();
    }

    public function scoreDifference()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return abs($homeTeamScore - $awayTeamScore);
    }

    public function parseHandicapSelection(): string
    {
        $rawHandicap = $this->rawHandicap();

        if (! str_contains($rawHandicap, self::HANDICAP_DELIMITER)) {
            return $rawHandicap;
        }

        [$firstHandicap, $secondHandicap] = explode(self::HANDICAP_DELIMITER, $rawHandicap);

        $handicap = ($firstHandicap + $secondHandicap) / 2;

        if ($handicap > 0) {
            return "+" . $handicap;
        }

        return (string) $handicap;
    }

    public function isFullGoalLine($handicap)
    {
        return (float) $this->getHandicapRemainder($handicap) === (float) self::FULL_GOAL_REMAINDER;
    }

    public function isHalfGoalLine($handicap)
    {
        return (float) $this->getHandicapRemainder($handicap) === (float) self::HALF_GOAL_REMAINDER;
    }

    public function isQuarterGoalLine($handicap)
    {
        return $this->getHandicapRemainder($handicap) === self::QUARTER_GOAL_LOWER_REMAINDER
            || $this->getHandicapRemainder($handicap) === self::QUARTER_GOAL_UPPER_REMAINDER;
    }

    public function isQuarterGoalLower($handicap)
    {
        return $this->getHandicapRemainder($handicap) === self::QUARTER_GOAL_LOWER_REMAINDER;
    }

    public function isQuarterGoalUpper($handicap)
    {
        return $this->getHandicapRemainder($handicap) === self::QUARTER_GOAL_UPPER_REMAINDER;
    }

    public function getHandicapRemainder($handicap): string
    {
        return (string) abs(fmod($handicap, self::HANDICAP_REMAINDER_DIVISOR));
    }

    public function isUnderdogHandicap($handicap): bool
    {
        return $handicap >= self::QUARTER_GOAL_LOWER_REMAINDER;
    }
}
