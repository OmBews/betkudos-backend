<?php

namespace App\Models\Events;

use App\Models\Countries\Country;
use App\Models\Leagues\League;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Results\Result;
use App\Models\Events\Stats\Stats;
use App\Models\Sports\Sport;
use App\Models\Teams\Team;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

trait Relationships
{
    public function home(): HasOne
    {
        return $this->hasOne(Team::class, 'bet365_id', 'home_team_id');
    }

    public function away(): HasOne
    {
        return $this->hasOne(Team::class, 'bet365_id', 'away_team_id');
    }

    public function markets()
    {
        return $this->hasMany(MatchMarket::class, 'match_id');
    }

    public function liveMarkets()
    {
        return $this->markets()->whereHas('market', function ($query) {
            $query->where('on_live_betting', true)
                ->where('active', true)
                ->with(['market']);
        });
    }

    public function odds(): HasMany
    {
        return $this->hasMany(MarketOdd::class, 'match_id');
    }

    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class, 'league_id', 'bet365_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'cc', 'code');
    }

    public function result(): HasOne
    {
        return $this->hasOne(Result::class, 'match_id');
    }

    public function stats(): HasOne
    {
        return $this->hasOne(Stats::class, 'match_id');
    }
}
