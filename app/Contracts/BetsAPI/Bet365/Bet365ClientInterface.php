<?php

namespace App\Contracts\BetsAPI\Bet365;

use Illuminate\Http\Client\Response;

interface Bet365ClientInterface
{
    public function inPlay(array $params = []): Response;

    public function inPlayFilter(array $params = []): Response;

    public function inPlayEvent(int $fixtureId, array $params = []): Response;

    public function upcoming(int $sportId, array $params = []): Response;

    public function preMatch(int $fixtureId, array $params = []): Response;

    public function result(int $eventId): Response;
}
