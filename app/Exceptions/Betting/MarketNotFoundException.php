<?php

namespace App\Exceptions\Betting;

use Exception;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MarketNotFoundException extends NotFoundHttpException
{
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (! $message) {
            $message = trans('bets.market.not_found');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
