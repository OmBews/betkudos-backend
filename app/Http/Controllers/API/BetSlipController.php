<?php

namespace App\Http\Controllers\API;

use App\Contracts\Services\BetSlipService;
use App\Http\Controllers\Controller;
use App\Http\Requests\BetSlip\UpdateRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BetSlipController extends Controller
{
    /**
     * @var BetSlipService
     */
    private $service;

    public function __construct(BetSlipService $service)
    {
        $this->service = $service;
    }

    public function update(UpdateRequest $request)
    {
        return JsonResource::collection($this->service->update($request));
    }
}
