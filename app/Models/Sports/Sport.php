<?php

namespace App\Models\Sports;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use App\Models\Leagues\League;
use App\Models\Markets\Market;
use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class League
 * @package App\Models\Sports
 *
 * @method static self|\Illuminate\Database\Eloquent\Builder active($isActive = true)
 * @property bool hasLeaguesAsCategories
 * @property bool includeAzList
 * @property bool showAllMatches
 * @property SportCategory[]|Collection categories
 * @property Market[]|Collection featured
 * @property Market[]|Collection markets
 * @property Market[]|Collection liveMarkets
 */
class Sport extends Model
{
    use UsesMemSQLConnection;

    /**
     * The period (in the future) that the matches
     * from this sport will be searched.
     *
     * @var int
     */
    public const DEFAULT_UPDATE_TIME_FRAME = 7;

    /**
     *  A limit for how many matches will be
     *  loaded on desktop matches preview
     *  for live/upcoming matches.
     */
    public const DEFAULT_PREVIEW_LIMIT = 3;

    public const AMERICAN_FOOTBALL_SPORT_ID = 12;
    public const BASEBALL_SPORT_ID = 16;
    public const BASKETBALL_SPORT_ID = 18;
    public const BOXING_MMA_SPORT_ID = 9;
    public const CRICKET_SPORT_ID = 3;
    public const DARTS_SPORT_ID = 15;
    public const E_SPORTS_SPORT_ID = 151;
    public const FUTSAL_SPORT_ID = 83;
    public const HANDBALL_SPORT_ID = 78;
    public const ICE_HOCKEY_SPORT_ID = 17;

    /**
     *  As we have the same id on betsapi for MMA and Boxing
     *  We will use 999 for MMA just to have a fixed id
     *  and avoid to change our entire codebase.
     */
    public const MMA_SPORT_ID = 999;

    public const RUGBY_LEAGUE_SPORT_ID = 19;
    public const RUGBY_UNION_SPORT_ID = 8;
    public const SOCCER_SPORT_ID = 1;
    public const SNOOKER_SPORT_ID = 14;
    public const TABLE_TENNIS_SPORT_ID = 92;
    public const TENNIS_SPORT_ID = 13;
    public const VOLLEYBALL_SPORT_ID = 91;

    public const SET_BASED_SPORTS = [
        self::TENNIS_SPORT_ID,
        self::VOLLEYBALL_SPORT_ID
    ];

    public const HAS_COUNTDOWN_TIMER = [
        self::BASKETBALL_SPORT_ID,
    ];

    protected $casts = [
        'active' => 'boolean',
        'on_live_betting' => 'boolean',
    ];

    protected $hidden = [
        'active', 'time_frame', 'created_at',
        'upcoming_preview_limit', 'live_preview_limit', 'on_live_betting',
        'updated_at', 'bet365_id'
    ];

    public function markets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Market::class, 'sport_id', 'bet365_id');
    }

    public function liveMarkets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->markets()->where('on_live_betting', true);
    }

    public function categories(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(SportCategory::class);
    }

    public function matches(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Event::class);
    }

    public function getTimeFrame(): int
    {
        return $this->getAttribute('time_frame');
    }

    public function getShowAllMatchesAttribute()
    {
        return $this->getTimeFrame() > 14;
    }

    public function getHasLeaguesAsCategoriesAttribute()
    {
        return (bool) $this->categories->count();
    }

    public function getIncludeAzListAttribute()
    {
        return !in_array($this->getKey(), [self::MMA_SPORT_ID, self::BOXING_MMA_SPORT_ID]);
    }

    public function buildTimeFrameString(): string
    {
        return "+{$this->time_frame} days";
    }

    public function isActive(): bool
    {
        return $this->getAttribute('active');
    }

    public function isCricket(): bool
    {
        return $this->getKey() === self::CRICKET_SPORT_ID;
    }

    public static function hasSetBasedScore(int $sportID): bool
    {
        return in_array($sportID, self::SET_BASED_SPORTS);
    }

    public static function hasCountdownTimer(int $sportID): bool
    {
        return in_array($sportID, self::HAS_COUNTDOWN_TIMER);
    }

    /**
     * Scope a query include only active sports.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param boolean $isActive
     * @return \Illuminate\Database\Eloquent\Builder|Sport
     */
    public function scopeActive($query, bool $isActive = true)
    {
        return $query->where('active', $isActive);
    }

    /**
     * Scope a query include only sports on live betting.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param boolean $isActive
     * @return \Illuminate\Database\Eloquent\Builder|Sport
     */
    public function scopeOnLiveBetting($query, bool $isActive = true)
    {
        return $query->where('on_live_betting', $isActive);
    }

    public function featured(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->markets()->where('featured', 1)->orderBy('priority', 'ASC');
    }
}
