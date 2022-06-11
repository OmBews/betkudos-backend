<?php

namespace App\Exceptions\Betting;

use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UnavailableSelectionException extends BadRequestHttpException
{
    public function __construct(?string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (!$message) {
            $message = trans('bets.odds.unavailable');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
