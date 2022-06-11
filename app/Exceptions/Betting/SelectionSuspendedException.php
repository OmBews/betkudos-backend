<?php

namespace App\Exceptions\Betting;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SelectionSuspendedException extends BadRequestHttpException
{
    public function __construct(?string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (! $message) {
            $message = trans('bets.odds.suspended');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
