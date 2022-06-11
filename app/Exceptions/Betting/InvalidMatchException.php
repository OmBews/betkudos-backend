<?php

namespace App\Exceptions\Betting;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class InvalidMatchException extends NotFoundHttpException
{
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (! $message) {
            $message = trans('bets.matches.invalid_or_not_found');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
