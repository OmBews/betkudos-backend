<?php

namespace App\Contracts\Services\BetsAPI;

use Amp\Promise;
use App\Contracts\Http\Clients\BetsAPI\Bet365ClientInterface;
use Psr\Http\Message\ResponseInterface;

interface Bet365ServiceInterface
{
    public function __construct(Bet365ClientInterface $client);

    public function makeUpcomingRequest($daysInFuture, int $sportId, int $page = null, int $leagueId = null): Promise;

    public function makeResultRequest(int $fixtureId, int $matchId): Promise;

    public function makePreMatchOddsRequest(int $fixtureId, bool $raw = false): ResponseInterface;
}
