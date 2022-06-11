<?php

namespace App\Contracts\Repositories;

use App\Http\Clients\BetsAPI\Responses\Bet365\ResultResponse;
use App\Models\Events\Results\Result;

interface ResultRepository
{
    public function __construct(Result $model);

    public function findByMatchId(int $matchId): ?Result;

    public function findByBet365Id(int $fixtureId): ?Result;

    public function createFromResultResponse(ResultResponse $response): ?Result;

    public function updateFromResultResponse(ResultResponse $response): bool;
}
