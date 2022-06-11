<?php

namespace App\Models\Events\Results;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Result
 * @package App\Models\Events\Results
 *
 * @property int match_id
 * @property string|null single_score
 * @property string|array scores
 * @property int points
 * @property int bet365_match_id
 * @property int|bool is_playing
 * @property string|int kick_of_time
 * @property int passed_minutes
 * @property int passed_seconds
 * @property int current_time
 * @property int quarter
 */
class Result extends Model
{
    use UsesMemSQLConnection;

    protected $table = 'match_results';

    protected $fillable = [
        'match_id', 'single_score', 'scores', 'points',
        'bet365_match_id', 'is_playing', 'kick_of_time',
        'passed_minutes', 'passed_seconds', 'current_time',
        'quarter',
    ];

    protected $casts = [
        'is_playing' => 'boolean'
    ];
}
