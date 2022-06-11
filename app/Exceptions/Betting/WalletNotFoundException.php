<?php

namespace App\Exceptions\Betting;

use Exception;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class WalletNotFoundException extends BadRequestHttpException
{
    public function __construct(?string $message = '', \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        if (!$message) {
            $message = trans('bets.wallet_not_found');
        }

        parent::__construct($message, $previous, $code, $headers);
    }
}
