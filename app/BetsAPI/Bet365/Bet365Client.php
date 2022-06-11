<?php

namespace App\BetsAPI\Bet365;

use App\Contracts\BetsAPI\Bet365\Bet365ClientInterface;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Bet365Client implements Bet365ClientInterface
{
    private string $token;

    private array $endpoints;

    public function __construct(string $token)
    {
        $this->token = $token;
        $this->endpoints = config('betsapi.bet365');
    }

    public function inPlay(array $params = []): Response
    {
        return $this->request($this->endpoints['inplay'], $params);
    }

    public function inPlayFilter(array $params = []): Response
    {
        return $this->request($this->endpoints['inplay_filter'], $params);
    }

    public function inPlayEvent(int $fixtureId, array $params = []): Response
    {
        return $this->request($this->endpoints['inplay_event'], ['FI' => $fixtureId, ...$params]);
    }

    public function upcoming(int $sportId, array $params = []): Response
    {
        return $this->request($this->endpoints['upcoming'], ['sport_id' => $sportId, ...$params]);
    }

    public function preMatch(int $fixtureId, array $params = []): Response
    {
        return $this->request($this->endpoints['prematch'], ['FI' => $fixtureId, ...$params]);
    }

    public function result(int $eventId): Response
    {
        return $this->request($this->endpoints['result'], ['event_id' => $eventId]);
    }

    private function http(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl(config('betsapi.endpoint'))->timeout(5);
    }

    private function request(string $url, array $params = []): Response
    {
        return $this->http()->get($url, array_merge($params, ['token' => $this->token]));
    }
}
