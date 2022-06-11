<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'provider' => $this->provider,
            'name' => $this->name,
            'image' => $this->image_url,
            'type' => $this->type,
            'category' => $this->category,
            'availIn' => $this->has_currency,
        ];
    }
}
