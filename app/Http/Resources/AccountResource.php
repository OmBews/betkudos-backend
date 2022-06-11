<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $resource = [
            'email' => $this->email,
            'username' => $this->username,
            'balance' => $this->balance,
            'type' => $this->type,
            'google2fa_enabled' => $this->google2fa_enabled,
            'wallets' => WalletResource::collection($this->wallets),
        ];

        if (!$this->email_verified_at) {
            $resource['email_verified_at'] = $this->email_verified_at;
        }

        return $resource;
    }
}
