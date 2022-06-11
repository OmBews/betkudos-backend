<?php

namespace App\Models\Teams;

use App\Concerns\MemSQL\UsesMemSQLConnection;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use UsesMemSQLConnection;

    protected $fillable = [
        'id', 'bet365_id', 'name',
        'bets_api_id', 'image_id', 'cc'
    ];

    protected $hidden = [
        'bet365_id', 'bets_api_id', 'created_at',
        'updated_at'
    ];
}
