<?php

namespace App\Models\Leagues;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use App\Models\Leagues\Traits\Relationships;
use App\Models\Leagues\Traits\Scopes;
use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class League
 * @package App\Models\Leagues
 *
 * @method static self|Builder whereHasMatchesFromAContinent(int $sportId = null)
 * @method static self|Builder whereHasUpcomingMatches(int $sportId = null, $leaguesIds = null, $limit = '')
 * @method static self|Builder active(bool $active = true)
 * @method static self|Builder sport(int $sportId)
 * @method static self|Builder popular(bool $popular = true)
 */
class League extends Model
{
    use Relationships;
    use Scopes;
    use UsesMemSQLConnection;

    /**
     * The period (in the future) that the matches
     * from this sport will be searched.
     *
     * @var int
     */
    public const DEFAULT_UPDATE_TIME_FRAME = 7;

    protected $fillable = [
        'bet365_id', 'name', 'sport_id',
        'bets_api_id', 'cc'
    ];

    protected $hidden = [
        'bet365_id', 'bets_api_id', 'created_at',
        'sport_id', 'time_frame', 'active',
        'updated_at', 'popular'
    ];
}
