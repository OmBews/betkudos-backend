<?php

namespace App\Models\FAQs;

use App\Models\FAQs\Traits\HasRelationships;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasRelationships;

    protected $fillable = [
        'question', 'answer', 'welcome',
        'priority', 'user_id', 'last_editor_id'
    ];

    protected $casts = [
      'welcome' => 'boolean'
    ];
}
