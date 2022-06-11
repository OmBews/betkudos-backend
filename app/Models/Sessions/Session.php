<?php

namespace App\Models\Sessions;

use App\Models\Sessions\Traits\Relationships;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Model;

class Session extends Model
{
    use Relationships;

    protected $fillable = [
        'user_id',
        'oauth_access_token_id',
        'device_id',
        'ip_address'
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['token', 'user', 'device', 'notAllowedIp'];

    public const PER_PAGE = 30;

    public function getByUserRestrictionFilter(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
