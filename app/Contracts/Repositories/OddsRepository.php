<?php

namespace App\Contracts\Repositories;

use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use Illuminate\Database\Eloquent\Model;

interface OddsRepository
{
    public function __construct(MarketOdd $model, Market $market);

    public function fullTimeResult(int $matchId): ?Model;
}
