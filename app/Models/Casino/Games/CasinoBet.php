<?php

namespace App\Models\Casino\Games;

use App\Models\Casino\Games\Game;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasinoBet extends Model
{
    use HasFactory;

    public function win()
    {
        return $this->hasOne(CasinoWin::class, 'round_id', 'round_id');
    }

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'player_id');
    }

    public function game_session()
    {
        return $this->hasOne(GameSession::class, 'id', 'session_id');
    }

    public function game()
    {
        return $this->hasMany(Game::class, 'aggregator_uuid', 'game_uuid');
    }

    public function casino()
    {
        return $this->hasOne(Game::class, 'aggregator_uuid', 'game_uuid');
    }

    public function betAmount()
    {
        return $this->hasMany(CasinoBet::class, 'game_uuid', 'game_uuid');
    }

    public function winCount()
    {
        return $this->hasMany(CasinoWin::class, 'session_id', 'session_id');
    }

    public function winCountForGameList()
    {
        return $this->hasMany(CasinoWin::class, 'game_uuid', 'game_uuid');
    }

    public function betCountForUser()
    {
        return $this->hasMany(CasinoBet::class, 'player_id', 'player_id');
    }

    public function winCountForUser()
    {
        return $this->hasMany(CasinoWin::class, 'player_id', 'player_id');
    }

    public function getDateDiff($timeframe)
    {
        $today = date("Y-m-d");

        return match ($timeframe) {

            "daily" => date("Y-m-d"),

            "weekly" => date('Y-m-d', strtotime($today . ' - 7 days')),

            "monthly" => date('Y-m-d', strtotime($today . ' - 31 days')),

            "yearly" => date('Y-m-d', strtotime($today . ' - 365 days')),

            default => date('Y-m-d', strtotime($today . ' - 365 days'))
        };
    }
}
