<?php

namespace App\Http\Resources;

use App\Contracts\Repositories\MatchRepository;
use App\Models\Countries\Country;
use App\Models\Events\Event;
use Illuminate\Http\Resources\Json\JsonResource;

class FeedResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'matches' => MatchResource::collection($this->matches),
            'cc' => $this->cc,
            'country' => $this->country
        ];
    }
}
