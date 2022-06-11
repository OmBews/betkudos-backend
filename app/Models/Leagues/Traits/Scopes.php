<?php

namespace App\Models\Leagues\Traits;

use App\Models\Countries\Country;
use App\Models\Events\Event;

trait Scopes
{
    /**
     * Scope a query include only leagues from a
     * continent that have upcoming matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasMatchesFromAContinent($query, int $sportId = null)
    {
        return $query->whereIn('cc', Country::CONTINENTS_CODES)
                      ->where('sport_id', $sportId)
                      ->whereHas('matches', function ($query) {
                          $query->upcoming();
                      })
                      ->active();
    }

    /**
     * Scope a query include only leagues from a
     * continent that also could have upcoming matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @param bool $hasMatches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromAContinent($query, int $sportId = null, $hasMatches = true)
    {
        $query = $query->whereIn('cc', Country::CONTINENTS_CODES)
                       ->where('sport_id', $sportId)
                       ->active();

        return $hasMatches ? $query->whereHas('matches', function ($query) {
            $query->upcoming();
        }) : $query;
    }

    /**
     * Scope a query include only leagues from a
     * country that also could have upcoming matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $countryCode
     * @param int|null $sportId
     * @param bool $hasMatches
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromACountry($query, string $countryCode, int $sportId = null, $hasMatches = true)
    {
        $query = $query->where('cc', $countryCode)
                       ->where('sport_id', $sportId)
                       ->active();

        return $hasMatches ? $query->whereHas('matches', function ($query) {
            $query->upcoming();
        }) : $query;
    }

    /**
     * Scope a query include only leagues
     * that are active or not
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $active
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $active = true)
    {
        return $query->where('active', $active);
    }

    /**
     * Scope a query include only leagues
     * that are active or not
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $sportId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSport($query, int $sportId)
    {
        return $query->where('sport_id', $sportId);
    }

    /**
     * Scope a query include only leagues
     * that are popular or not
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param bool $popular
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePopular($query, bool $popular = true)
    {
        return $query->where('popular', $popular);
    }

    /**
     * Scope a query to filter leagues where there are
     * upcoming matches
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @param int|array|null $leaguesIds
     * @param string $limit
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasUpcomingMatches(
        $query,
        $sportId = null,
        $leaguesIds = null,
        $limit = Event::UPCOMING_DAYS_LIMIT
    ) {
        if ($sportId) {
            $query->where('sport_id', $sportId);
        }

        return $query->whereHas('matches', function ($query) use ($leaguesIds, $limit, $sportId) {
            $query->upcoming($sportId, $leaguesIds, $limit);
        });
    }
}
