<?php

namespace App\Exceptions\Betting;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class OddsHasChangedException extends BadRequestHttpException
{
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (! $message) {
            $message = trans('bets.odds.changes');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
