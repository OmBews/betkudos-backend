<?php

namespace App\Models\Sessions\Logs;

use App\Models\Sessions\Logs\Traits\Relationships;
use App\Models\NotAllowedIps\NotAllowedIp;
use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

class SessionLog extends Model
{
    use Relationships;

    protected $fillable = [
        'user_id',
        'session_id',
        'ip_address',
        'action',
    ];

    public const ACTION_LOGIN = 'LOGIN';

    public const ACTION_LOGOUT = 'LOGOUT';

    public const ACTION_2FA_ENABLED = '2FA_ENABLED';

    public const ACTION_2FA_DISABLED = '2FA_DISABLED';

    public const ACTION_PASSWORD_UPDATED = 'PASSWORD_UPDATED';

    public const ACTION_EMAIL_UPDATED = 'EMAIL_UPDATED';

    public const ACTIONS = [
        self::ACTION_LOGIN,
        self::ACTION_LOGOUT,
        self::ACTION_2FA_ENABLED,
        self::ACTION_2FA_DISABLED,
        self::ACTION_PASSWORD_UPDATED,
        self::ACTION_EMAIL_UPDATED
    ];

    public const LOGIN_ACTIONS = [
        self::ACTION_LOGIN,
        self::ACTION_LOGOUT,
    ];

    public const PER_PAGE = 30;
}
