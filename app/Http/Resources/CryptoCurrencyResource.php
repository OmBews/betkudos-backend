<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CryptoCurrencyResource extends JsonResource
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
            'name' => $this->name,
            'ticker' => $this->ticker,
            'icon_url' => $this->icon_url,
            'places' => $this->places,
            'network_fee' => $this->network_fee,
        ];
    }
}
