<?php

namespace App\Events\Auth;

use App\Contracts\Events\Auth\SessionLogger;
use App\Models\Sessions\Logs\SessionLog;
use App\Models\Users\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Jenssegers\Agent\Agent;

class Logout extends SessionLogger
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @param User $user
     * @param Agent $agent
     * @param Request $request
     */
    public function __construct(User $user, Agent $agent, Request $request)
    {
        parent::__construct(
            $user,
            $agent,
            SessionLog::ACTION_LOGOUT,
            $request->ip()
        );
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
