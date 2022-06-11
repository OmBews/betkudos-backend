<?php

namespace App\Models\Casino\Games;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameCategory extends Model
{
    use HasFactory;

    public function games()
    {
        return $this->hasMany(Game::class, 'main_category', 'name');
    }
}
