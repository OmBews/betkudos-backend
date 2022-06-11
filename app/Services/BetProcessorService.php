<?php

namespace App\Services;

use App\Jobs\Matches\ProcessResults;
use App\Models\Bets\Bet;
use App\Contracts\Services\BetProcessorService as BetProcessorServiceInterface;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Events\Event;
use App\Processors\Processor;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class BetProcessorService implements BetProcessorServiceInterface
{
    private const HALF_STAKE_DIVISOR = 2;

    /**
     * @inheritDoc
     */
    public function process(Bet $bet)
    {
        $user = $bet->user;
        $selections = $bet->selections;
        $selectionsStatuses = [];

        if (! $this->areAllSelectionsEligibleToBeProcessed($selections)) {
            foreach ($selections as $selection) {
                ProcessResults::dispatchNow($selection->match);

                $selection->match->result->fresh();
                if ($selection->match->stats) {
                    $selection->match->stats->fresh();
                }
            }

            if (! $this->areAllSelectionsEligibleToBeProcessed($selections)) {
                return BetSelection::STATUS_OPEN;
            }
        }

        foreach ($selections as $selection) {
            $status = BetSelection::STATUS_OPEN;

            if ($this->isMatchStatusVoidable($selection)) {
                $status = BetSelection::STATUS_VOID;
            } else {
                try {
                    $status = Processor::factory($selection, $selection->market)->process();
                } catch (\Throwable $e) {
                    Log::alert($e->getMessage());
                    Log::alert("Can't process a bet for market: {$selection->market->name},{$selection->market->key}");
                    throw $e;
                }
            }

            $selectionsStatuses[] = $status;
            $selection->status = $status;
            $selection->save();
        }


        $status = $this->determineBetStatus($selectionsStatuses, $selections);

        if ($this->areThereVoidSelections($selections) && !$this->areAllSelectionsVoid($selections)) {
            $bet->profit = $this->getBetProfit($selections, $bet->stake);
        }

        if ($status === Bet::STATUS_VOID) {
            $bet->wallet->balance += $bet->stake;
        }

        if ($status === Bet::STATUS_WON) {
            $bet->wallet->balance += $bet->profit;
        } elseif ($status === Bet::STATUS_HALF_WON) {
            $bet->stake = $bet->stake / self::HALF_STAKE_DIVISOR;
            $bet->profit = $this->getBetProfit($selections, $bet->stake, true);
            $bet->wallet->balance += $bet->profit;
        } elseif ($status === Bet::STATUS_HALF_LOST) {
            $bet->stake = $bet->stake / self::HALF_STAKE_DIVISOR;
            $bet->wallet->balance += $bet->stake;
        }

        $bet->status = $status;

        $bet->save();
        $bet->wallet->save();

        return $status;
    }

    /**
     * @param array $statuses
     * @param BetSelection[]|Collection $selections
     * @return string
     */
    private function determineBetStatus(array $statuses, $selections): string
    {
        if (in_array(Bet::STATUS_OPEN, $statuses)) {
            return Bet::STATUS_OPEN;
        }

        if ($this->areThereLostSelections($selections)) {
            return Bet::STATUS_LOST;
        }

        if ($this->areAllSelectionsVoid($selections) && $this->areAllMatchesEnded($selections)) {
            return Bet::STATUS_VOID;
        }

        if ($this->areAllSelectionsWon($this->nonVoidableSelections($selections))) {
            return Bet::STATUS_WON;
        }

        if ($this->areAllSelectionsHalfLost($selections)) {
            return Bet::STATUS_HALF_LOST;
        }

        if ($this->areAllSelectionsHalfWon($selections)) {
            return Bet::STATUS_HALF_WON;
        }

        return Bet::STATUS_OPEN;
    }

    private function getBetProfit(Collection $selections, $stake, bool $halfOdds = false)
    {
        $eligibleSelections = $this->nonVoidableSelections($selections);

        $accumulator = 0;

        foreach ($eligibleSelections as $selection) {
            if ($accumulator === 0) {
                $accumulator = $halfOdds ? (float) $selection->odds / 2 : (float) $selection->odds;
            } else {
                $accumulator *= $halfOdds ? (float) $selection->odds / 2 : (float) $selection->odds;
            }
        }

        return $accumulator * $stake;
    }

    private function nonVoidableSelections(Collection $selections)
    {
        $filter = function ($selection) {
            return $selection->status !== BetSelection::STATUS_VOID;
        };

        return $selections->filter($filter);
    }

    private function isMatchStatusVoidable(BetSelection $selection)
    {
        return in_array($selection->match->time_status, self::VOIDABLE_STATUSES);
    }

    private function areThereLostSelections(Collection $selections)
    {
        $filter = function ($selection) {
            return $selection->status === BetSelection::STATUS_LOST;
        };

        return $selections->filter($filter)->count() >= 1;
    }

    /**
     * @param Collection $selections
     * @return bool
     */
    private function areAllMatchesEnded(Collection $selections): bool
    {
        $filter = function ($selection) {
            // It should check for voidable status again
            // to make sure the bet will be voided
            // So we will consider this ended too.
            if ($this->isMatchStatusVoidable($selection)) {
                return true;
            }

            return (int) $selection->match->time_status === Event::STATUS_ENDED;
        };

        $selectionsEnded = $selections->filter($filter);

        return $selectionsEnded->count() === $selections->count();
    }

    /**
     * @param Collection $selections
     * @return bool
     */
    private function areAllSelectionsWon(Collection $selections): bool
    {
        $filter = function ($selection) {
            return $selection->status === BetSelection::STATUS_WON;
        };

        return $selections->filter($filter)->count() === $selections->count();
    }

    /**
     * @param Collection $selections
     * @return bool
     */
    private function areAllSelectionsHalfWon(Collection $selections): bool
    {
        $filter = function ($selection) {
            return $selection->status === BetSelection::STATUS_HALF_WON;
        };

        return $selections->filter($filter)->count() === $selections->count();
    }

    /**
     * @param Collection $selections
     * @return bool
     */
    private function areAllSelectionsHalfLost(Collection $selections): bool
    {
        $filter = function ($selection) {
            return $selection->status === BetSelection::STATUS_HALF_LOST;
        };

        return $selections->filter($filter)->count() === $selections->count();
    }

    /**
     * @param Collection $selections
     * @return bool
     */
    private function areAllSelectionsVoid(Collection $selections): bool
    {
        $filter = function ($selection) {
            return $selection->status === BetSelection::STATUS_VOID;
        };

        return $selections->filter($filter)->count() === $selections->count();
    }

    private function areAllScoresAndStatsAvailable($selections)
    {
        foreach ($selections as $selection) {
            if ($selection->match->result->single_score === null || $selection->match->stats === null) {
                return false;
            }
        }

        return true;
    }

    private function areAllSelectionsEligibleToBeProcessed($selections)
    {
        return $this->areAllMatchesEnded($selections) && $this->areAllScoresAndStatsAvailable($selections);
    }

    private function areThereVoidSelections(Collection $selections)
    {
        $filter = function ($selection) {
            return $selection->status === BetSelection::STATUS_VOID;
        };

        return $selections->filter($filter)->count() > 0;
    }
}
