<?php

namespace App\Processors\AsianLines;

use App\Contracts\Processors\AsianLines\AsianHandicapProcessorInterface as AsianHandicapProcessor;
use App\Contracts\Processors\AsianLines\AsianLine;
use App\Contracts\Processors\AbstractSelectionProcessor as Processor;
use App\Models\Bets\Selections\BetSelection;

class QuarterGoalAsianLine implements AsianLine
{
    /**
     * @var AsianHandicapProcessor
     */
    private $processor;

    /**
     * @var string
     */
    private $handicap;

    public function __construct(AsianHandicapProcessor $processor, string $handicap)
    {
        $this->processor = $processor;
        $this->handicap = $handicap;
    }

    public function status(): string
    {
        if ($this->processor->isUnderdogHandicap($this->handicap)) {
            return $this->determineUnderdogTeamStatus();
        }

        return $this->determinePreferredTeamStatus();
    }

    public function determineUnderdogTeamStatus(): string
    {
        $result = $this->processor->getSelectionResult();
        $goalsNeededToWin = (float) abs(round($this->handicap));

        if ($result === Processor::SELECTION_RESULT_WON) {
            return BetSelection::STATUS_WON;
        } elseif ($result === Processor::SELECTION_RESULT_DRAW) {
            if ($this->processor->isQuarterGoalLower($this->handicap)) {
                if ((float) $this->processor->scoreDifference() === $goalsNeededToWin) {
                    // Should return STATUS_HALF_WON status
                    return BetSelection::STATUS_HALF_WON;
                }
            }

            return BetSelection::STATUS_WON;
        } elseif ($result === Processor::SELECTION_RESULT_LOST) {
            if ($this->processor->isQuarterGoalLower($this->handicap)) {
                if ((float) $this->processor->scoreDifference() < $goalsNeededToWin) {
                    return BetSelection::STATUS_WON;
                } elseif ((float) $this->processor->scoreDifference() === $goalsNeededToWin) {
                    // Should return STATUS_HALF_WON status
                    return BetSelection::STATUS_HALF_WON;
                }
            } else {
                if ((float) $this->processor->scoreDifference() < $goalsNeededToWin) {
                    return BetSelection::STATUS_WON;
                } elseif ((float) $this->processor->scoreDifference() === $goalsNeededToWin) {
                    // Should return STATUS_HALF_LOST status
                    return BetSelection::STATUS_HALF_LOST;
                }
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    public function determinePreferredTeamStatus(): string
    {
        $result = $this->processor->getSelectionResult();
        $goalsNeededToWin = (float) abs(round($this->handicap));

        if ($result === Processor::SELECTION_RESULT_LOST) {
            return BetSelection::STATUS_LOST;
        } elseif ($result === Processor::SELECTION_RESULT_DRAW) {
            if ($this->processor->isQuarterGoalLower($this->handicap)) {
                if ((float) $this->processor->scoreDifference() === $goalsNeededToWin) {
                    // Should return STATUS_HALF_LOST status
                    return BetSelection::STATUS_HALF_LOST;
                }
            }

            return BetSelection::STATUS_LOST;
        } elseif ($result === Processor::SELECTION_RESULT_WON) {
            if ($this->processor->isQuarterGoalLower($this->handicap)) {
                if ((float) $this->processor->scoreDifference() > $goalsNeededToWin) {
                    return BetSelection::STATUS_WON;
                } elseif ((float) $this->processor->scoreDifference() === $goalsNeededToWin) {
                    // Should return HALF_LOST
                    return BetSelection::STATUS_HALF_LOST;
                }

                return BetSelection::STATUS_LOST;
            } else {
                if ((float) $this->processor->scoreDifference() > $goalsNeededToWin) {
                    return BetSelection::STATUS_WON;
                } elseif ((float) $this->processor->scoreDifference() === $goalsNeededToWin) {
                    // Should return HALF_WON
                    return BetSelection::STATUS_HALF_WON;
                }

                return BetSelection::STATUS_LOST;
            }
        }

        return BetSelection::STATUS_OPEN;
    }
}
