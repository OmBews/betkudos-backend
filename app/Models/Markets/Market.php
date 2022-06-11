<?php

namespace App\Models\Markets;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use App\Models\Sports\Sport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Class Market
 * @package App\Models\Markets
 *
 * @method static self|\Illuminate\Database\Eloquent\Builder whereHasOdds(int $matchId)
 * @method static self|\Illuminate\Database\Eloquent\Builder withMatchOdds(int $matchesIds)
 * @method static self|\Illuminate\Database\Eloquent\Builder popular(bool $popular = true)
 *
 * @property Sport sport
 * @property MarketOdd[] odds
 */
class Market extends Model
{
    use UsesMemSQLConnection;

    protected $hidden = [
        'created_at', 'updated_at', 'active',
        'on_live_betting',
        'market_groups'
    ];

    protected $casts = [
        'popular' => 'boolean',
        'featured' => 'boolean',
        'on_live_betting' => 'boolean',
        'headers' => 'array',
    ];

    public const FULL_TIME_RESULT_KEY = 'full_time_result';

    public const FULL_TIME_RESULT_MODEL_CACHE_KEY = 'full_time_result_model';

    public const BOTH_TO_SCORE_KEY = 'both_teams_to_score';

    public const BOTH_TO_SCORE_MODEL_CACHE_KEY = 'both_teams_to_score_model';

    private const MARKETS_KEYS_CACHE_KEY = 'market_keys';

    public const DEFAULT_LAYOUT = 1;

    public const INLINE_LAYOUT = 2;

    public const OVER_UNDER_LAYOUT = 3;

    public const GOALSCORERS_LAYOUT = 4;

    public const SCORE_LAYOUT = 5;

    public function fullTimeResult(): Model
    {
        if (! $model = Cache::get(self::FULL_TIME_RESULT_MODEL_CACHE_KEY)) {
            $model = $this->newQuery()
                          ->where('key', self::FULL_TIME_RESULT_KEY)
                          ->first();

            Cache::put(self::FULL_TIME_RESULT_MODEL_CACHE_KEY, $model, strtotime('+2 days'));
        }

        return $model;
    }

    public function bothToScore(): Model
    {
        if (! $model = Cache::get(self::BOTH_TO_SCORE_MODEL_CACHE_KEY)) {
            $model = $this->newQuery()
                          ->where('key', self::BOTH_TO_SCORE_KEY)
                          ->first();

            Cache::put(self::BOTH_TO_SCORE_MODEL_CACHE_KEY, $model, strtotime('+2 days'));
        }

        return $model;
    }

    public static function activeKeys(int $sportId): Collection
    {
        $cacheKey = self::MARKETS_KEYS_CACHE_KEY . 'sport_' . $sportId;
        if (! $keys = Cache::get($cacheKey)) {
            $keys = self::query()
                  ->where('active', true)
                  ->where('sport_id', $sportId)
                  ->get(['key', 'id', 'priority']);

            Cache::put($cacheKey, $keys, 3600);
        }

        return $keys;
    }

    public function odds()
    {
        return $this->hasMany(MarketOdd::class);
    }

    public function sport()
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Scope a query include only markets
     * that are popular or not.
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
     * Scope a query include only markets
     * that have no empty odds.
     *
     * @param self|\Illuminate\Database\Eloquent\Builder $query
     * @param int $matchId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasOdds($query, int $matchId)
    {
        return $query->whereHas('odds', function ($query) use ($matchId) {
            $query->where('match_id', $matchId);
        });
    }

    /**
     * Scope a query include only markets
     * that have no empty odds with your
     * related match.
     *
     * @param self|\Illuminate\Database\Eloquent\Builder $query
     * @param int|array $matchesIds
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithMatchOdds($query, $matchesIds, $isLive = false)
    {
        return $query->with(['odds' => function ($query) use ($isLive, $matchesIds) {
            $attributes = [
                'id', 'match_id', 'market_id',
                'name', 'odds', 'handicap', 'header'
            ];

            if (is_array($matchesIds)) {
                return $query->whereIn('match_id', $matchesIds)->select($attributes);
            }

            return $query->where('match_id', $matchesIds)->where('is_live', $isLive)->select($attributes);
        }]);
    }
}
