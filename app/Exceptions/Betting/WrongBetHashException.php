<?php

namespace App\Exceptions\Betting;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class WrongBetHashException extends UnprocessableEntityHttpException
{
    public function __construct(?string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (!$message) {
            $message = trans('bets.wrong_hash');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
