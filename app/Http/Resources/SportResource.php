<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SportResource extends JsonResource
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
            'leagues' => $this->when(isset($this->leagues), function () {
                return FeedResource::collection($this->leagues);
            }),
            'featured' => $this->when(isset($this->featured), $this->featured),
            'showAllMatches' => $this->showAllMatches,
            'hasLeaguesAsCategories' => $this->hasLeaguesAsCategories,
            'includeAzList' => $this->includeAzList,
            'count' => $this->when(isset($this->count), function () {
                return $this->count;
            })
        ];
    }
}
