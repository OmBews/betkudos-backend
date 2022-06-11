<?php

namespace App\Models\Bets\Selections;

use App\Models\Bets\Bet;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Model;

/**
 * Class BetSelection
 * @package App\Models\Bets\Selections
 *
 * @property Bet|\Illuminate\Database\Eloquent\Relations\BelongsTo bet
 * @property Event|\Illuminate\Database\Eloquent\Relations\BelongsTo match
 * @property Market|\Illuminate\Database\Eloquent\Relations\BelongsTo market
 * @property MarketOdd|\Illuminate\Database\Eloquent\Relations\HasMany|float odds
 * @property string|null name
 * @property string|null header
 * @property string|null handicap
 */
class BetSelection extends Model
{
    public const STATUS_OPEN = 'open';

    public const STATUS_VOID = 'void';

    public const STATUS_LOST = 'lost';

    public const STATUS_WON = 'won';

    public const STATUS_HALF_WON = 'half_won';

    public const STATUS_HALF_LOST = 'half_lost';

    public const STATUSES = [
        self::STATUS_OPEN,
        self::STATUS_VOID,
        self::STATUS_LOST,
        self::STATUS_WON,
        self::STATUS_HALF_WON,
        self::STATUS_HALF_LOST,
    ];

    protected $fillable = [
        'match_id', 'market_id', 'odd_id',
        'odds', 'name', 'header',
        'handicap'
    ];

    protected $casts = [
        'odds' => 'decimal:2',
    ];

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }

    public function match()
    {
        return $this->belongsTo(Event::class, 'match_id', 'id');
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    public function marketOdd(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(MarketOdd::class, 'id', 'odd_id');
    }
}
