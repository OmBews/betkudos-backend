<?php

namespace App\Models\Promotions\Traits;

use Illuminate\Support\Facades\Storage;

trait Mutators
{
    public function getImageUrlAttribute(): string
    {
        return Storage::disk('promotions')->url($this->image);
    }
}
