<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WithdrawResource extends JsonResource
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
            'userKyc' => $this->userKyc,
            'currency' => $this->currency,
            'address' => $this->address,
            'amount' => $this->amount,
            'fee' => $this->fee,
            'txid' => $this->txid,
            'status' => $this->status,
            'automatic' => $this->is_automatic,
            'created_at' => $this->created_at,
            'confirmed_at' => $this->confirmed_at,
        ];
    }
}
