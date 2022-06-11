<?php

namespace App\Http\Resources;

use App\Contracts\Repositories\SessionLogRepository;
use App\Repositories\SessionRepository;
use Illuminate\Http\Resources\Json\JsonResource;

class SessionResource extends JsonResource
{
    public function __construct($resource)
    {
        parent::__construct($resource);
    }

    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function toArray($request)
    {
        $logs = app()->make(SessionLogRepository::class);

        $lastLogIn = $logs->logIn($this->id);
        $lastLogOut = $logs->logOut($this->id);

        $token = $this->token;

        return [
            'id' => $this->id,
            'user' => $this->user,
            'userStatus' => $this->user->status(),
            'ipAddress' => $this->ip_address,
            'ipAllowed' => $this->notAllowedIp === null,
            'device' => $this->device,
            'expires_at' => $token ? $token->expires_at : null,
            'logInTime' => $lastLogIn ? $lastLogIn->created_at : null,
            'logOutTime' => $lastLogOut ? $lastLogOut->created_at : null,
        ];
    }
}
