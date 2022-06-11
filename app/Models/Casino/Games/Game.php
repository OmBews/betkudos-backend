<?php

namespace App\Models\Casino\Games;

use App\Models\Casino\Providers\Provider;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    public function category()
    {
        return $this->belongsTo(GameCategory::class, 'main_category', 'name');
    }

    public function favourite()
    {
        return $this->hasMany(Favourite::class, 'game_id', 'id');
    }

    public function provider()
    {
        return $this->hasMany(Favourite::class, 'provider', 'provider');
    }

    public function gameProvider()
    {
        return $this->hasOne(Provider::class, 'name', 'provider');
    }

    public function bet(){
        return $this->belongsTo(CasinoBet::class, 'game_uuid', 'aggregator_uuid');
    }

    public function getUuid(){
        return $this->hasMany(CasinoBet::class, 'game_uuid', 'aggregator_uuid');
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image;
        // if ($this->image !== '/default.jpg') {
        //     return config('filesystems.disks.digital_ocean.casino_cdn').$this->image;
        // }

        // return config('filesystems.disks.digital_ocean.casino_cdn').'/casino'.$this->image;
    }
}
