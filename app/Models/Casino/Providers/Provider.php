<?php

namespace App\Models\Casino\Providers;

use App\Models\Casino\Games\Game;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Provider extends Model
{
    use HasFactory;

    public function providerHasGame () {
        return $this->hasMany(Game::class, 'provider', 'name');
    }
}
