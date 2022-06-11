<?php

namespace App\Services\BetsAPI;

use Amp\Promise;
use App\Contracts\Http\Clients\BetsAPI\Bet365ClientInterface;
use App\Contracts\Services\BetsAPI\Bet365ServiceInterface;
use App\Http\Clients\BetsAPI\Responses\Bet365\ResultResponse;
use App\Http\Clients\BetsAPI\Responses\Bet365\UpcomingResponse;
use Psr\Http\Message\ResponseInterface;

class Bet365Service implements Bet365ServiceInterface
{
    /**
     * @var Bet365ClientInterface
     */
    private $client;

    public function __construct(Bet365ClientInterface $client)
    {
        $this->client = $client;
    }

    public function makeUpcomingRequest($daysInFuture, int $sportId, int $page = null, int $leagueId = null): Promise
    {
        return promise(function () use ($sportId, $daysInFuture, $page, $leagueId) {
            $day = is_int($daysInFuture) ? $this->client::formatSearchDate($daysInFuture) : $daysInFuture;

            $res = yield $this->client->upcoming($sportId, $day, $page, $leagueId);

            return UpcomingResponse::factory(yield $res->getBody(), $sportId, $day, $leagueId);
        });
    }

    public function makeResultRequest(int $fixtureId, int $matchId): Promise
    {
        return promise(function () use ($matchId, $fixtureId) {
            $res = yield $this->client->result($fixtureId);

            return ResultResponse::factory(yield $res->getBody(), $fixtureId, $matchId);
        });
    }

    public function makePreMatchOddsRequest(int $fixtureId, bool $raw = false): ResponseInterface
    {
        return $this->client->preMatch($fixtureId, $raw);
    }
}
