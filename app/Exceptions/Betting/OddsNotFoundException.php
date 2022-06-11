<?php

namespace App\Exceptions\Betting;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OddsNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (! $message) {
            $message = trans('bets.odds.not_found');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
