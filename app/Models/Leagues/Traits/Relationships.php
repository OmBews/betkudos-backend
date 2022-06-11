<?php

namespace App\Models\Leagues\Traits;

use App\Models\Countries\Country;
use App\Models\Events\Event;
use App\Models\Sports\Sport;

trait Relationships
{
    public function matches()
    {
        return $this->hasMany(Event::class, 'league_id', 'bet365_id');
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'cc', 'code');
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class, 'sport_id', 'id');
    }
}
