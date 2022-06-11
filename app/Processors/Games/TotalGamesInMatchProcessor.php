<?php


namespace App\Processors\Games;


use App\Processors\Goals\GoalsOverUnderProcessor;
use App\Services\MatchEventsService;

class TotalGamesInMatchProcessor extends GoalsOverUnderProcessor
{
    protected function goals(): float
    {
        $events = $this->selection->match->stats->events;

        if (!is_array($events) && is_string($events)) {
            $events = json_decode($events);
        }

        $exception = new \Exception('Unavailable events, can not process the given selection.');

        if (!$events || !count($events)) {
            throw $exception;
        }

        $service = MatchEventsService::factory($this->selection->match->stats->events);

        if (!$service->lastGame()) {
            throw $exception;
        }

        return (float) $service->lastGame();
    }
}
