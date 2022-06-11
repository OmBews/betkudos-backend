<?php

namespace App\Processors\AsianLines;

use App\Contracts\Processors\AbstractSelectionProcessor as Processor;
use App\Contracts\Processors\AsianLines\AsianHandicapProcessorInterface;
use App\Contracts\Processors\AsianLines\AsianLine;
use App\Models\Bets\Selections\BetSelection;

class FullGoalAsianLine implements AsianLine
{
    /**
     * @var AsianHandicapProcessorInterface
     */
    private $processor;

    /**
     * @var string
     */
    private $handicap;

    public function __construct(AsianHandicapProcessorInterface $processor, string $handicap)
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

        if ($result === Processor::SELECTION_RESULT_DRAW || $result === Processor::SELECTION_RESULT_WON) {
            return BetSelection::STATUS_WON;
        } elseif ($result === Processor::SELECTION_RESULT_LOST) {
            if ((float) $this->processor->scoreDifference() === (float) $this->handicap) {
                return BetSelection::STATUS_VOID;
            } elseif ($this->processor->scoreDifference() < $this->handicap) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }

    public function determinePreferredTeamStatus(): string
    {
        $result = $this->processor->getSelectionResult();

        if ($result === Processor::SELECTION_RESULT_DRAW || $result === Processor::SELECTION_RESULT_LOST) {
            return BetSelection::STATUS_LOST;
        } elseif ($result === Processor::SELECTION_RESULT_WON) {
            if ((float) $this->processor->scoreDifference() === (float) abs($this->handicap)) {
                return BetSelection::STATUS_VOID;
            } elseif ($this->processor->scoreDifference() > abs($this->handicap)) {
                return BetSelection::STATUS_WON;
            }

            return BetSelection::STATUS_LOST;
        }

        return BetSelection::STATUS_OPEN;
    }
}
