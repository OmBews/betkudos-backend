<?php

namespace App\Processors\AsianLines;

use App\Contracts\Processors\AsianLines\AsianHandicapProcessorInterface as AsianHandicapProcessor;
use App\Contracts\Processors\AsianLines\AsianLine;
use App\Contracts\Processors\AbstractSelectionProcessor as Processor;
use App\Models\Bets\Selections\BetSelection;

class HalfGoalAsianLine implements AsianLine
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

        if ($result === Processor::SELECTION_RESULT_WON) {
            return BetSelection::STATUS_WON;
        } elseif ($result === Processor::SELECTION_RESULT_LOST || $result === Processor::SELECTION_RESULT_DRAW) {
            $goalsNeededToWin = $this->handicap - AsianHandicapProcessor::HALF_GOAL_REMAINDER;

            if ($this->processor->scoreDifference() <= $goalsNeededToWin) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    public function determinePreferredTeamStatus(): string
    {
        $result = $this->processor->getSelectionResult();

        if ($result === Processor::SELECTION_RESULT_LOST || $result === Processor::SELECTION_RESULT_DRAW) {
            return BetSelection::STATUS_LOST;
        } elseif ($result === Processor::SELECTION_RESULT_WON) {
            $goalsNeededToWin = abs($this->handicap) + (float) AsianHandicapProcessor::HALF_GOAL_REMAINDER;

            if ($this->processor->scoreDifference() >= $goalsNeededToWin) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }
}
