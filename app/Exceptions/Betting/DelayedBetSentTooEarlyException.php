<?php

namespace App\Exceptions\Betting;

use LogicException;
use Throwable;

class DelayedBetSentTooEarlyException extends LogicException
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (!$message) {
            $message = trans('bets.delayed_bet_sent_too_early');
        }

        parent::__construct($message, $code, $previous);
    }
}
