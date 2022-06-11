<?php

namespace App\Contracts\Events\Auth;

use App\Models\Users\User;
use Jenssegers\Agent\Agent;

abstract class SessionLogger
{
    public $user;

    public $agent;

    public $action;

    public $ipAddress;

    public function __construct(User $user, Agent $agent, string $action, string $ipAddress)
    {
        $this->user = $user;
        $this->agent = $agent;
        $this->action = $action;
        $this->ipAddress = $ipAddress;
    }
}
