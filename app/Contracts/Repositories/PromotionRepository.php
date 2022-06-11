<?php

namespace App\Contracts\Repositories;

use App\Contracts\Repositories\Concerns\Prioritizable;
use App\Models\Promotions\Promotion;

interface PromotionRepository extends Prioritizable
{
    public function __construct(Promotion $promotion);
}
