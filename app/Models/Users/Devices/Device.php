<?php

namespace App\Models\Users\Devices;

use Illuminate\Database\Eloquent\Model;
use Jenssegers\Agent\Agent;

class Device extends Model
{
    public const TYPE_DESKTOP = 'DESKTOP';

    public const TYPE_MOBILE = 'MOBILE';

    public const TYPE_TABLET = 'TABLET';

    public const TYPE_ROBOT = 'ROBOT';

    public const DEVICE_TYPES = [
        self::TYPE_DESKTOP,
        self::TYPE_MOBILE,
        self::TYPE_TABLET,
        self::TYPE_ROBOT,
    ];

    protected $fillable = [
        'user_id', 'name','user_agent',
        'browser','platform' ,'type',
    ];

    protected $hidden = [
        'id', 'user_id', 'created_at',
        'updated_at'
    ];

    public static function getType(Agent $agent): string
    {
        if ($agent->isDesktop()) {
            return self::TYPE_DESKTOP;
        }

        if ($agent->isTablet()) {
            return self::TYPE_TABLET;
        }

        if ($agent->isPhone()) {
            return self::TYPE_MOBILE;
        }

        return self::TYPE_ROBOT;
    }
}
