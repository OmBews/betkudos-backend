<?php


namespace App\Services;


use App\Contracts\Services\MatchEventsServiceInterface;
use Illuminate\Support\Arr;

class MatchEventsService implements MatchEventsServiceInterface
{
    private array $events;

    private const GAME_PATTERN = '/(Game \d+ -)/';

    public function __construct(array|string $events)
    {
        if (!is_array($events)) {
            $this->events = json_decode($events);

            return;
        }

        $this->events = $events;
    }

    public static function factory(array|string $events)
    {
        return new static($events);
    }

    public function currentGame(): string|int
    {
        $games = array_filter($this->events, function ($event) {
            $matches = [];

            return preg_match(self::GAME_PATTERN, $event->text, $matches) === 1;
        });

        $game = Arr::first(array_reverse($games));

        if (!$game) {
            return 1;
        }

        $lastGame = preg_filter('/ -.*$/', '', $game->text);

        $lastGame = preg_filter('/[Game ]/', '', $lastGame);

        if (is_string($lastGame)) {
            return (int) $lastGame + 1;
        }

        return 1;
    }

    public function lastGame(): int
    {
        return (int) $this->currentGame() - 1;
    }
}
