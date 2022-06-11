<?php

namespace App\Models\Currencies;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static Builder ticker(string $ticker)
 */
class CryptoCurrency extends Model
{
    use HasFactory;

    public const TICKER_BTC = 'BTC';
    public const TICKER_USDT = 'USDT';
    public const TICKER_PLAY = 'PLAY';

    public function getIconUrlAttribute()
    {
        return asset($this->icon);
    }

    public function scopeTicker($query, string $ticker)
    {
        return $query->where('ticker', $ticker);
    }

    public function toGbp($value)
    {
        return $value * $this->gbp_price;
    }

    public function toEur($value)
    {
        return $value * $this->eur_price;
    }
}
