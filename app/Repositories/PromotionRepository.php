<?php

namespace App\Repositories;

use App\Models\Promotions\Promotion;
use App\Contracts\Repositories\PromotionRepository as PromotionRepositoryContract;
use App\Repositories\Concerns\Prioritizable;

class PromotionRepository extends Repository implements PromotionRepositoryContract
{
    use Prioritizable;

    public function __construct(Promotion $model)
    {
        parent::__construct($model);
    }
}
