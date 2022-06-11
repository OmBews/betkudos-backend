<?php

namespace App\Models\Markets;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MarketGroup extends Model
{
    use UsesMemSQLConnection;

    private const GROUPS_KEYS_CACHE_KEY = 'market_groups_keys';

    public static function activeKeys(): array
    {
        if (! $keys = Cache::get(self::GROUPS_KEYS_CACHE_KEY)) {
            $keys = self::query()
                        ->where('active', true)
                        ->get(['key'])
                        ->pluck('key')
                        ->toArray();

            Cache::put(self::GROUPS_KEYS_CACHE_KEY, $keys, 3600);
        }

        return $keys;
    }
}
