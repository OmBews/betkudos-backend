<?php

namespace App\Exceptions\Betting;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class NoBetPlaced extends BadRequestHttpException
{
    public function __construct(string $message = null, \Throwable $previous = null, int $code = 0, array $headers = [])
    {
        parent::__construct($message, $previous, $code, $headers);
    }
}
