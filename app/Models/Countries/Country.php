<?php

namespace App\Models\Countries;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use App\Models\Leagues\League;
use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Country
 * @package App\Models\Countries
 *
 * @method static self|Builder withActiveLeagues(int $sportId = null)
 * @method static self|Builder whereIsInternational
 * @method static self|Builder whereIsInternationalClubs
 * @method static self|Builder whereHasMatches(int $sportId = null)
 * @method static self|Builder whereHasLeaguesWithMatches(int $sportId = null)
 *
 */
class Country extends Model
{
    use UsesMemSQLConnection;

    public const INTERNATIONAL_COUNTRY_CODE = '01';

    public const INTERNATIONAL_CLUBS_COUNTRY_CODE = '02';

    public const INTERNATIONAL_CLUBS_FRIENDLIES_COUNTRY_CODE = '03';

    public const TEMPORARY_COUNTRY_CODE = '04';

    public const EUROPE_CODE = 'eu';

    public const CONTINENTS_CODES = [
        self::EUROPE_CODE
    ];

    protected $hidden = [
        'created_at', 'updated_at'
    ];

    protected $fillable = [
        'name', 'code'
    ];

    public function leagues()
    {
        return $this->hasMany(League::class, 'cc', 'code');
    }

    public function matches()
    {
        return $this->hasMany(Event::class, 'cc', 'code');
    }

    public static function international()
    {
        return self::query()
            ->where('code', self::INTERNATIONAL_COUNTRY_CODE)
            ->first();
    }

    public static function internationalClubs()
    {
        return self::query()
            ->where('code', self::INTERNATIONAL_CLUBS_COUNTRY_CODE)
            ->first();
    }

    public function isInternationalClubs(): bool
    {
        return $this->getAttribute('code') === self::INTERNATIONAL_CLUBS_COUNTRY_CODE;
    }

    /**
     * Scope a query include active leagues.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function scopeWithActiveLeagues($query, int $sportId = null)
    {
        return $query->with(['leagues' => function (HasMany $query) use ($sportId) {
            $query = $sportId ?? $query->where('sport_id', $sportId);

            $query->where('active', true);
        }]);
    }

    /**
     * Scope a query include "International countries"
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsInternational($query)
    {
        return $query->where('code', '=', self::INTERNATIONAL_COUNTRY_CODE);
    }

    /**
     * Scope a query include "International clubs"
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsInternationalClubs($query)
    {
        return $query->where('code', '=', self::INTERNATIONAL_CLUBS_COUNTRY_CODE);
    }

    /**
     * Scope a query include only countries where has
     * upcoming matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @return \Illuminate\Database\Eloquent\Builder
     */

    public function scopeWhereHasMatches($query, int $sportId = null)
    {
        return $query->whereHas('matches', function ($query) use ($sportId) {
            return $query->upcoming($sportId);
        });
    }

    /**
     * Scope a query include only countries where has
     * leagues with upcoming matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasLeaguesWithMatches($query, int $sportId = null)
    {
        return $query->whereHas('leagues', function (Builder $query) use ($sportId) {
            $query->where('active', true)
                  ->whereHas('matches', function (Builder $query) {
                      return $query->upcoming();
                  });

            return $sportId ? $query->where('sport_id', $sportId) : $query;
        });
    }

    /**
     * Scope a query include country leagues and matches
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithLeaguesWhereHasMatches($query, int $sportId = null)
    {
        return $query->with(['leagues' => function (Builder $query) use ($sportId) {
            $query->where('active', true)
                  ->whereHas('matches', function (Builder $query) {
                      return $query->upcoming();
                  });

            return $sportId ? $query->where('sport_id', $sportId) : $query;
        }]);
    }
}
