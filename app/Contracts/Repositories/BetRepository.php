<?php

namespace App\Contracts\Repositories;

use App\Models\Bets\Bet;
use Illuminate\Support\Collection;

interface BetRepository
{
    public function __construct(Bet $model);

    /**
     * @param int|null $userId
     * @param array $relations
     * @return Bet[]|Collection
     */
    public function open(int $userId = null, array $relations = []);
}
