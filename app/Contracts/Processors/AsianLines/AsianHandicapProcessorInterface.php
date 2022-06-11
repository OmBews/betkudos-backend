<?php

namespace App\Contracts\Processors\AsianLines;

interface AsianHandicapProcessorInterface
{
    public const FULL_GOAL_REMAINDER = "0.00";
    public const HALF_GOAL_REMAINDER = "0.50";
    public const QUARTER_GOAL_LOWER_REMAINDER = "0.25";
    public const QUARTER_GOAL_UPPER_REMAINDER = "0.75";

    /**
     * @return string
     * @throws \Exception
     */
    public function getSelectionResult(): string;

    public function scores(): array;

    public function isFullGoalLine($handicap);

    public function isHalfGoalLine($handicap);

    public function isQuarterGoalLine($handicap);

    public function isQuarterGoalLower($handicap);

    public function isQuarterGoalUpper($handicap);

    public function getHandicapRemainder($handicap): string;

    public function isUnderdogHandicap($handicap): bool;

    public function parseHandicapSelection(): string;

    public function scoreDifference();
}
