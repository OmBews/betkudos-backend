<?php

namespace App\Contracts\Repositories;

use App\Models\Users\Devices\Device;
use App\Models\Users\User;
use Jenssegers\Agent\Agent;

interface DeviceRepository
{
    public function __construct(Device $device);

    public function createIfNotExists(Agent $agent, User $user): Device;

    public function findByUserIdAndAgent(int $userId, Agent $agent): ?Device;
}
