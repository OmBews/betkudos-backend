<?php

namespace App\Models\Events\Stats;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use Illuminate\Database\Eloquent\Model;

class Stats extends Model
{
    use UsesMemSQLConnection;

    protected $table = 'match_stats';

    protected $fillable = [
        'match_id', 'stats', 'events'
    ];

    protected $casts = [
      'stats' => 'object',
    ];
}
