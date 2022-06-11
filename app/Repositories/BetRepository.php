<?php

namespace App\Repositories;

use App\Models\Bets\Bet;
use App\Contracts\Repositories\BetRepository as BetRepositoryInterface;

class BetRepository extends Repository implements BetRepositoryInterface
{

    public function __construct(Bet $model)
    {
        parent::__construct($model);
    }

    /**
     * @inheritDoc
     */
    public function open(int $userId = null, array $relations = [])
    {
        $query = $this->newQuery();

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->with($relations)->get();
    }
}
