<?php

namespace App\Models\Sports;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use App\Models\Leagues\League;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Class League
 * @package App\Models\Sports
 *
 * @property SportCategory[]|Collection leagues
 * @property Sport sport
 */
class SportCategory extends Model
{
    use UsesMemSQLConnection;

    use HasFactory;

    protected $with = ['sport'];

    public function sport(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    public function leagues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(League::class);
    }

    /**
     * @param Builder $query
     * @param string|null $timeFrame
     * @return Builder
     */
    public function scopeWhereHasLeaguesWithMatches($query, string $timeFrame = null): Builder
    {
        $query->whereHas('leagues', function ($query) use ($timeFrame) {
            $query->whereHasUpcomingMatches(null, null, $timeFrame);
        });

        return $query;
    }
}
