<?php

namespace App\Repositories;

use App\Contracts\Repositories\ResultRepository as ResultRepositoryInterface;
use App\Http\Clients\BetsAPI\Responses\Bet365\ResultResponse;
use App\Models\Events\Results\Result;

class ResultRepository extends Repository implements ResultRepositoryInterface
{
    public function __construct(Result $model)
    {
        parent::__construct($model);
    }

    public function create(array $attributes)
    {
        try {
            return parent::create($attributes);
        } catch (\PDOException | \Exception $exception) {
            if ($exception->getCode() === "23000" && isset($attributes['bet365_match_id'])) {
                return $this->findByBet365Id($attributes['bet365_match_id']);
            }

            throw $exception;
        }
    }

    public function findByMatchId(int $matchId): ?Result
    {
        return $this->model
                    ->newQuery()
                    ->where('match_id', $matchId)
                    ->first();
    }

    public function findByBet365Id(int $fixtureId): ?Result
    {
        return $this->model
                    ->newQuery()
                    ->where('bet365_match_id', $fixtureId)
                    ->first();
    }

    public function createFromResultResponse(ResultResponse $response): ?Result
    {
        $result = $this->findByBet365Id($response->getFixtureId());

        if ($result instanceof Result) {
            return $result;
        }

        $attrs = [
            'match_id' => $response->getMatchId(),
            'bet365_match_id' => $response->getFixtureId(),
            'single_score' => $response->getSingleScore(),
            'scores' => json_encode($response->getScores()) ?? '{}',
        ];

        return $this->create($attrs);
    }

    public function updateFromResultResponse(ResultResponse $response): bool
    {
        $attrs = [
            'single_score' => $response->getSingleScore(),
            'scores' => $response->getScores(),
        ];

        $result = $this->findByMatchId($response->getMatchId());

        if (! $result) {
            return false;
        }

        return $this->update($result->getKey(), $attrs);
    }
}
