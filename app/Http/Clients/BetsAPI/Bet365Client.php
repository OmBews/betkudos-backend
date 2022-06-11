<?php

namespace App\Http\Clients\BetsAPI;

use Amp\Promise;
use App\Contracts\Http\Clients\BetsAPI\Bet365ClientInterface;
use GuzzleHttp\Client;

final class Bet365Client extends BetsAPIClient implements Bet365ClientInterface
{
    public function __construct(string $token = null)
    {
        parent::__construct($token);

        $this->http->setOption(\Amp\Artax\Client::OP_TRANSFER_TIMEOUT, 60000);
    }

    public function inPlay(bool $raw = false): Promise
    {
        $query = [
            'raw' => $raw
        ];

        $uri = $this->baseUrl;
        $uri .= config('betsapi.bet365.inplay');

        return $this->get($this->buildUriQuery($uri, $query));
    }

    public function inPlayFilter(int $sportId = null, int $leagueId = null): Promise
    {
        $query = [
            'sport_id' => $sportId,
            'league_id' => $leagueId,
        ];

        $uri = $this->baseUrl;
        $uri .= config('betsapi.bet365.inplay_filter');

        return $this->get($this->buildUriQuery($uri, $query));
    }

    public function inPlayEvent(int $fixtureId, bool $stats = false, bool $lineUp = false, bool $raw = false): Promise
    {
        $query = [
            'FI' => $fixtureId,
            'stats' => $stats,
            'lineup' => $lineUp,
            'raw' => $raw,
        ];

        $uri = $this->baseUrl;
        $uri .= config('betsapi.bet365.inplay_filter');

        return $this->get($this->buildUriQuery($uri, $query));
    }

    public function upcoming(int $sportId, ?string $day = null, ?int $page = null, ?int $leagueId = null): Promise
    {
        $query = [
            'sport_id' => $sportId,
            'page' => $page,
            'league_id' => $leagueId,
            'day' => $day,
        ];

        $uri = $this->baseUrl;
        $uri .= config('betsapi.bet365.upcoming');

        return $this->get($this->buildUriQuery($uri, $query));
    }

    public function preMatch(int $fixtureId, bool $raw = true)
    {
        $client = new Client(['base_uri' => $this->baseUrl]);

        return $client->get(config('betsapi.bet365.prematch'), [
            'query' => [
                'FI' => $fixtureId,
                'token' => $this->token
            ]
        ]);
    }

    public function result(int $eventId): Promise
    {
        $query = [
            'event_id' => $eventId
        ];

        $uri = $this->baseUrl;
        $uri .= config('betsapi.bet365.result');

        return $this->get($this->buildUriQuery($uri, $query));
    }

    public static function formatSearchDate(int $days = 0): string
    {
        if ($days <= 0) {
            return date('Ymd');
        }

        return date('Ymd', strtotime("+$days days"));
    }
}
