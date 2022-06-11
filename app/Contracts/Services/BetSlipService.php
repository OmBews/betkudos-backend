<?php

namespace App\Contracts\Services;

use App\Http\Requests\BetSlip\UpdateRequest;
use Illuminate\Support\Collection;

interface BetSlipService
{
    /**
     * @param UpdateRequest $request
     * @return Collection|array
     */
    public function update(UpdateRequest $request);
}
