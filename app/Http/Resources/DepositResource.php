<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class DepositResource extends JsonResource
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
            'user' => $this->user,
            'created_at' => $this->created_at,
            'currency' => $this->currency,
            'address' => $this->address->address,
            'amount' => $this->amount,
            'txid' => $this->txid,
            'status' => $this->status,
            'senderAddress' => $this->senderAddress
        ];
    }
}
