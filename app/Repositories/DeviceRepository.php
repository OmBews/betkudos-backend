<?php

namespace App\Repositories;

use App\Contracts\Repositories\DeviceRepository as DeviceRepositoryContract;
use App\Models\Users\Devices\Device;
use App\Models\Users\User;
use Jenssegers\Agent\Agent;

class DeviceRepository extends Repository implements DeviceRepositoryContract
{
    public function __construct(Device $device)
    {
        parent::__construct($device);
    }

    public function createIfNotExists(Agent $agent, User $user): Device
    {
        $device = $this->findByUserIdAndAgent($user->getKey(), $agent);

        if ($device instanceof Device) {
            return $device;
        }

        $attrs = [
            'user_id' => $user->getKey(),
            'name' => $agent->device(),
            'user_agent' => $agent->getUserAgent() ?? 'Browser',
            'browser' => $agent->browser(),
            'platform' => $agent->platform(),
            'type' => Device::getType($agent),
        ];

        return $this->create($attrs);
    }

    public function findByUserIdAndAgent(int $userId, Agent $agent): ?Device
    {
        return $this->model
                    ->where('user_id', $userId)
                    ->where('name', $agent->device())
                    ->where('user_agent', $agent->getUserAgent())
                    ->where('browser', $agent->browser())
                    ->where('platform', $agent->platform())
                    ->where('type', Device::getType($agent))
                    ->first();
    }
}
