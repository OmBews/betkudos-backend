<?php

namespace App\Exceptions\Betting;

use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AsianHandicapOnMultipleException extends BadRequestHttpException
{
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (!$message) {
            $message = trans('bets.markets.asian_handicap_on_multiple');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
