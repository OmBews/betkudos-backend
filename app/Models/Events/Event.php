<?php

namespace App\Models\Events;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use App\Models\Leagues\League;
use App\Models\Markets\Market;
use App\Models\Events\Results\Result;
use App\Models\Events\Stats\Stats;
use App\Models\Sports\Sport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Class Match
 * @package App\Models\Matches
 *
 * @method static self|Builder upcoming(int $sport=null,int|array $league=null,string $limit = '',array $fields=[],string $tz = 'UTC')
 * @method static self|Builder live(int|array $sport=null,int|array $league=null,array $fields=[],string $tz = 'UTC')
 * @method static self|Builder whereHasOdds(array $marketIds = [], bool $isLive = true)
 * @property Result result
 * @property Stats stats
 * @property League league
 * @property Sport sport
 * @property MatchMarket[]|Collection liveMarkets
 */
class Event extends Model
{
    use Relationships;
    use UsesMemSQLConnection;

    protected $table = 'matches';

    public const STATUS_NOT_STARTED = 0;

    public const STATUS_IN_PLAY = 1;

    public const STATUS_TO_BE_FIXED = 2;

    public const STATUS_ENDED = 3;

    public const STATUS_POSTPONED = 4;

    public const STATUS_CANCELLED = 5;

    public const STATUS_WALKOVER = 6;

    public const STATUS_INTERRUPTED = 7;

    public const STATUS_ABANDONED = 8;

    public const STATUS_RETIRED = 9;

    public const STATUS_REMOVED = 99;

    public const UPCOMING_DAYS_LIMIT = '+5 days';

    public const TODAY_MATCHES_LIMIT = '23:59:59';

    public const STARTING_SOON_LIMIT = '+1 hour';

    protected $fillable = [
        'bet365_id', 'bets_api_id', 'sport_id',
        'home_team_id', 'away_team_id', 'league_id',
        'starts_at', 'time_status', 'last_bets_api_update',
        'cc'
    ];

    protected $hidden = [
        'bet365_id', 'bets_api_id', 'last_bets_api_update', 'created_at', 'updated_at'
    ];

    public function getBet365Id(): int
    {
        return $this->getAttribute('bet365_id');
    }

    public function getHomeTeamId(): int
    {
        return $this->getAttribute('home_team_id');
    }

    public function getAwayTeamId(): int
    {
        return $this->getAttribute('away_team_id');
    }

    public function getLeagueId(): int
    {
        return $this->getAttribute('league_id');
    }

    public function isEligibleToProcessLiveOdds(): bool
    {
        return in_array($this->time_status, [Event::STATUS_IN_PLAY, Event::STATUS_NOT_STARTED]);
    }

    public function isLive(): bool
    {
        return (int) $this->time_status === self::STATUS_IN_PLAY;
    }

    public function isNotStarted(): bool
    {
        return (int) $this->time_status === self::STATUS_NOT_STARTED;
    }

    /**
     * Scope a query include only upcoming matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $sportId
     * @param int|array|null $leaguesIds
     * @param string $limit
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming(
        $query,
        $sportId = null,
        $leaguesIds = null,
        $limit = self::UPCOMING_DAYS_LIMIT,
        array $fields = [],
        string $timezone = 'UTC'
    ) {
        $now = Carbon::now($timezone)->unix();

        $query->where('time_status', self::STATUS_NOT_STARTED)
              ->where('starts_at', '>', $now)
              ->where('starts_at', '<=', strtotime($limit, $now));

        if ($sportId) {
            $query->where('sport_id', $sportId);
        }

        if ($leaguesIds) {
            if (is_array($leaguesIds)) {
                $query->whereIn('league_id', $leaguesIds);
            } else {
                $query->where('league_id', $leaguesIds);
            }
        }

        if (count($fields)) {
            $query->select($fields);
        }

        return $query;
    }

    /**
     * Scope a query include only upcoming matches.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array|null $sportId
     * @param int|array|null $leaguesIds
     * @param array $fields
     * @return \Illuminate\Database\Eloquent\Builder
     * -230 minutes org
     */
    public function scopeLive($query, $sportId = null, $leaguesIds = null, array $fields = [], string $timezone = 'UTC')
    {
        $now = Carbon::now($timezone)->unix();
        $query->where('starts_at', '>=', strtotime('-230 minutes', $now))
            ->where('starts_at', '<=', strtotime('+10 minutes', $now))
            ->where('time_status', self::STATUS_IN_PLAY);
        
        if ($sportId) {
            if (is_array($sportId)) {
                $query->whereIn('sport_id', $sportId);
            } else {
                $query->where('sport_id', $sportId);
            }
        }

        if ($leaguesIds) {
            if (is_array($leaguesIds)) {
                $query->whereIn('league_id', $leaguesIds);
            } else {
                $query->where('league_id', $leaguesIds);
            }
        }

        if (count($fields)) {
            $query->select($fields);
        }

        return $query;
    }

    /**
     * Scope a query to include only those matches with odds.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|array|null $marketsIds
     * @param bool $isLive
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereHasOdds($query, $marketsIds = null, $isLive = true)
    {
        if (! $marketsIds) {
            return $query->whereHas('odds')->select(['match_id', 'market_id']);
        }

        return $query->whereHas('odds', function ($query) use ($isLive, $marketsIds) {
            $query->where('is_live', $isLive);

            if (is_array($marketsIds)) {
                return $query->whereIn('market_id', $marketsIds)->select(['match_id', 'market_id']);
            }

            return $query->where('market_id', $marketsIds)->select(['match_id', 'market_id']);
        });
    }
}
