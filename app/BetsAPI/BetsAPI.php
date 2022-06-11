<?php

namespace App\BetsAPI;

use Carbon\Carbon;

class BetsAPI
{
    public static function formatSearchDate(int $days = 0): string
    {
        if ($days <= 0) {
            return date('Ymd');
        }

        return date('Ymd', strtotime("+$days days"));
    }

    public static function kickOfTimeToUnix($kickOfTime): int
    {
        $year = substr($kickOfTime, 0, 4);
        $month = substr($kickOfTime, 4, 2);
        $day = substr($kickOfTime, 6, 2);

        $hour = substr($kickOfTime, 8, 2);
        $minutes = substr($kickOfTime, 10, 2);
        $seconds = substr($kickOfTime, 12, 2);

        return Carbon::create($year, $month, $day, $hour, $minutes, $seconds)->utc()->unix();
    }
}
