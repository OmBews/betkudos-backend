<?php

namespace App\Contracts\Services;

use App\Models\Bets\Bet;
use App\Models\Events\Event;

interface BetProcessorService
{
    public const VOIDABLE_STATUSES = [
        Event::STATUS_TO_BE_FIXED,
        Event::STATUS_POSTPONED,
        Event::STATUS_CANCELLED,
        Event::STATUS_WALKOVER,
        Event::STATUS_INTERRUPTED,
        Event::STATUS_ABANDONED,
        Event::STATUS_RETIRED,
        Event::STATUS_REMOVED
    ];

    /**
     * @param Bet $bet
     * @return string
     */
    public function process(Bet $bet);
}
