<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class BetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'type' => $this->type,
            'profit' => $this->profit,
            'stake' => $this->stake,
            'status' => $this->status,
            'selections' => SelectionResource::collection($this->selections),
            'placed_at' => $this->created_at,
            'wallet' => new WalletResource($this->wallet)
        ];
    }
}
