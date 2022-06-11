<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SelectionResource extends JsonResource
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
            'betId' => $this->bet_id,
            'match' => [
                'id' => $this->match_id,
                'home' => $this->match->home,
                'away' => $this->match->away,
                'date' => gmdate("Y-m-d\TH:i:s\Z", $this->match->starts_at),
            ],
            'market' => $this->market,
            'odds' => (float) $this->odds,
            'name' => $this->marketOdd->name,
            'header' => $this->when($this->marketOdd->header, $this->marketOdd->header),
            'handicap' => $this->when($this->marketOdd->handicap, $this->marketOdd->handicap),
            'status' => $this->status,
            'placed_at' => $this->created_at,
        ];
    }
}
