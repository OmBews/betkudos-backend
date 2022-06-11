<?php

namespace App\Models\Casino\Games;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CasinoRefund extends Model
{
    use HasFactory;

    public function bet(){
        return $this->belongsTo(CasinoBet::class);
    }
}
