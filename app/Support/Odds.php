<?php

namespace App\Support;

class Odds
{
    /**
     * @param string $odds
     * @param int $precision
     * @return int|float
     * @throws \DivisionByZeroError
     */
    public static function toDecimal(string $odds, int $precision = 3)
    {
        if ($odds === "0/0" || $odds === "") {
            return 0;
        }

        [$dividend, $divisor] = explode('/', $odds);

        return round(($dividend / $divisor) + 1, $precision);
    }
}
