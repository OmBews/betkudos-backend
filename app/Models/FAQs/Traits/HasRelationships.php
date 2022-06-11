<?php

namespace App\Models\FAQs\Traits;

use App\Models\Users\User;

trait HasRelationships
{
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lastEditor()
    {
        return $this->belongsTo(User::class, 'last_editor_id');
    }
}
