<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockTrack extends Model
{
    use HasFactory;

    protected $table = 'block_track';

    protected $fillable = [
        'crypto_currency_id'
    ];
}
