<?php

namespace App\Models\Events;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use Illuminate\Database\Eloquent\Model;

/**
 * Class MatchMarket
 * @package App\Models\Matches
 * @property Market market
 * @property MarketOdd[] odds
 * @property Event match
 */
class MatchMarket extends Model
{
    use UsesMemSQLConnection;

    protected $fillable = [
        'match_id', 'market_id', 'order'
    ];

    public function match(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function market(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Market::class);
    }

    public function odds(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(MarketOdd::class);
    }
}
