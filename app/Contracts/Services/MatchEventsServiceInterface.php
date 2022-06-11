<?php


namespace App\Contracts\Services;


interface MatchEventsServiceInterface
{
    public function __construct(string|array $events);

    public static function factory(string|array $events);

    public function currentGame(): string|int;

    public function lastGame(): int;
}
