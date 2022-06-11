<?php

namespace App\Services;

use App\BetsAPI\BetsAPI;
use App\Contracts\BetsAPI\Bet365\Bet365ClientInterface as Bet365Client;
use App\Exceptions\BetsAPI\APICallException;
use Illuminate\Support\Collection;

class MatchResultService
{
    /**
     * @var Bet365Client
     */
    private Bet365Client $bet365;

    public function __construct(Bet365Client $client)
    {
        $this->bet365 = $client;
    }

    public function result(int $eventId): object
    {
        $response = $this->bet365->result($eventId);

        if ($response->failed()) {
            $response->throw();
        }

        $data = $response->object();

        if (! $data->success) {
            throw new APICallException("$data->error - $data->error_detail");
        }

        return collect($data->results)->first();
    }

    public function resultToArrayModel($data): array
    {
        return [
            'single_score' => $data->ss ?? null,
            'scores' => $this->toString($data->scores ?? null, '{}'),
            'points' => $data->points ?? null,
            'current_time' => $this->isTimerAvailable($data) ? $data->timer->tm : 0,
            'is_playing' => $this->isTimerAvailable($data) ? $data->timer->tt === "1" : 0,
            'passed_minutes' => $this->isTimerAvailable($data) ? $data->timer->tm : 0,
            'passed_seconds' => $this->isTimerAvailable($data) ? $data->timer->ts : 0,
            'quarter' => $this->isTimerAvailable($data) && property_exists($data->timer, 'q') ? $data->timer->q : 0,
        ];
    }

    private function isTimerAvailable(object $data)
    {
        return property_exists($data, 'timer') && $data->timer;
    }

    public function statsToArrayModel($data): array
    {
        return [
            'stats' => $this->toString($data->stats ?? null, '[]'),
            'events' => $this->toString($data->events ?? null, '[]'),
        ];
    }

    public function matchToArrayModel($data): array
    {
        return [
            'cc' => $data->league->cc,
            'time_status' => $data->time_status,
            'starts_at' => $data->time,
        ];
    }

    protected function toString($value, $default)
    {
        return is_object($value) || is_array($value) ? json_encode($value) : $value ?? $default;
    }

    public function parseMatchTimeData(Collection $eventData): array
    {
        $event = $eventData->first(fn ($data) => $data->type === "EV");

        if (! $event) {
            return [];
        }

        $isPlaying = $event->TT === "1";

        if ($isPlaying) {
            $currentTime = (time() - BetsAPI::kickOfTimeToUnix($event->TU)) + ($event->TM * 60) + $event->TS;
        } else {
            $currentTime = ($event->TM * 60) + $event->TS;
        }

        return [
            'is_playing' => $isPlaying,
            'kick_of_time' => $event->TU ? $event->TU : 0,
            'passed_minutes' => $event->TM ? $event->TM : 0,
            'passed_seconds' => $event->TS ? $event->TS : 0,
            'current_time' => $currentTime,
        ];
    }
}
