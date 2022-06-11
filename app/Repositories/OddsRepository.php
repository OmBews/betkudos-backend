<?php

namespace App\Repositories;

use App\Contracts\Repositories\OddsRepository as OddsRepositoryContract;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class OddsRepository extends Repository implements OddsRepositoryContract
{
    /**
     * @var Market
     */
    private $market;

    /**
     * OddsRepository constructor.
     * @param MarketOdd $model
     * @param Market $market
     */
    public function __construct(MarketOdd $model, Market $market)
    {
        parent::__construct($model);

        $this->market = $market;
    }

    /**
     * @param int $matchId
     * @return Collection
     */
    public function fullTimeResult(int $matchId): ?Model
    {
        $fullTimeResult = $this->market->fullTimeResult();

        $query = $this->newQuery();

        return $query->where('market_id', $fullTimeResult->getKey())
                     ->where('match_id', $matchId)
                     ->first();
    }
}
