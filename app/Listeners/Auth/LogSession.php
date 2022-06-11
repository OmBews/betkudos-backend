<?php

namespace App\Listeners\Auth;

use App\Contracts\Events\Auth\SessionLogger;
use App\Contracts\Repositories\DeviceRepository as DeviceRepo;
use App\Contracts\Repositories\SessionLogRepository as SessionLogRepo;
use App\Contracts\Repositories\SessionRepository as SessionRepo;
use App\Events\Auth\Login;
use App\Models\Sessions\Logs\SessionLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Jenssegers\Agent\Agent;

class LogSession
{
    use SerializesModels;

    private $sessions;

    private $sessionLogs;

    private $devices;

    /**
     * Create the event listener.
     *
     * @param SessionRepo $sessions
     * @param SessionLogRepo $sessionLogs
     * @param DeviceRepo $devices
     */
    public function __construct(SessionRepo $sessions, SessionLogRepo $sessionLogs, DeviceRepo $devices)
    {
        $this->sessions = $sessions;
        $this->sessionLogs = $sessionLogs;
        $this->devices = $devices;
    }

    /**
     * Handle the event.
     *
     * @param SessionLogger $event
     * @return void
     */
    public function handle(SessionLogger $event)
    {
        try {
            $user = $event->user;
            $agent = $event->agent;
            $ipAddress = $event->ipAddress;

            $device = $this->devices->createIfNotExists($agent, $user);

            $tokenId = null;

            if ($event->action === SessionLog::ACTION_LOGIN) {
                $tokenId = $user->tokens()->first()->getKey();
            } else {
                $tokenId = $user->token()->getKey();
            }

            $session = $this->sessions->findByOauthAccessTokenId($tokenId);

            if (! $session) {
                $session = $this->sessions->createNew($user, $device, $tokenId, $ipAddress);
            }

            $log = [
                'user_id' => $user->getKey(),
                'session_id' => $session->getKey(),
                'ip_address' => $ipAddress,
                'action' => $event->action,
            ];


            $this->sessionLogs->create($log);
        } catch (\Exception $exception) {
            throw $exception;
            //Log::error($exception->getMessage());
        }
    }
}
