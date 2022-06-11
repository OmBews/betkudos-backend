<?php

namespace App\Models\Bets;

use App\Models\Bets\Selections\BetSelection;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Class Bet
 * @package App\Models\Bets
 *
 * @property User|\Illuminate\Database\Eloquent\Relations\BelongsTo user
 * @property Wallet|\Illuminate\Database\Eloquent\Relations\BelongsTo wallet
 * @property BetSelection[]|Collection|\Illuminate\Database\Eloquent\Relations\BelongsTo selections
 *
 * @method static self|\Illuminate\Database\Eloquent\Builder open()
 */
class Bet extends Model
{
    public const TYPE_SINGLE = 'single';

    public const TYPE_MULTIPLE = 'multiple';

    public const TYPES = [
      self::TYPE_SINGLE,
      self::TYPE_MULTIPLE
    ];

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
        'user_id', 'code', 'type',
        'stake', 'profit', 'live',
        'wallet_id', 'ip_address'
    ];

    protected $casts = [
        'stake' => 'decimal:8',
        'profit' => 'decimal:8'
    ];

    /**
     * @return string
     */
    public static function genUniqueCode()
    {
        $code = Str::upper(Str::random(10));

        while (self::query()->where('code', '=', $code)->first('code')) {
            $code = Str::upper(Str::random(10));
        }

        return $code;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function selections()
    {
        return $this->hasMany(BetSelection::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class)->with('currency');
    }

    /**
     * Scope a query include only open bets
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }
}
