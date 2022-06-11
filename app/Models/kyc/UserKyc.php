<?php

namespace App\Models\kyc;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserKyc extends Model
{
    use HasFactory;

    public function documents(){
        return $this->hasMany(Document::class, 'user_id', 'user_id');
    }
}
