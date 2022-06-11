<?php

namespace App\Models\Markets;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MarketOdd
 * @package App\Models\Markets
 *
 * @method static self|\Illuminate\Database\Eloquent\Builder whereIsNotEmpty(int $matchId)
 * @method static self|\Illuminate\Database\Eloquent\Builder fromMatch(int $matchId)
 */
class MarketOdd extends Model
{
    use UsesMemSQLConnection;

    protected $table = 'odds';

    protected $fillable = [
        'market_id', 'match_id', 'name',
        'header', 'handicap', 'odds',
        'bet365_id', 'match_market_id', 'is_suspended'
    ];

    protected $hidden = [
      'created_at', 'updated_at', 'bet365_id'
    ];

    protected $casts = [
        'odds' => 'float',
        'is_live' => 'boolean',
        'is_suspended' => 'boolean',
    ];

    public function market()
    {
        return $this->belongsTo(Market::class);
    }

    /**
     * Scope a query include only odds from
     * a specific match.
     *
     * @param self|\Illuminate\Database\Eloquent\Builder $query
     * @param int $matchId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromMatch($query, int $matchId)
    {
        return $query->where('match_id', $matchId);
    }

    /**
     * Scope a query include only where has
     * no empty odds.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $matchId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereIsNotEmpty($query, int $matchId)
    {
        return $query->whereJsonLength('data', '>', 0)
                     ->where('match_id', $matchId);
    }
}
