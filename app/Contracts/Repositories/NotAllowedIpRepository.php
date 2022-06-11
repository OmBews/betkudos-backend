<?php

namespace App\Contracts\Repositories;

use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Sessions\Session;
use App\Models\Users\User;

interface NotAllowedIpRepository
{
    public function __construct(NotAllowedIp $model);

    public function find(string $ipAddress): ?NotAllowedIp;

    public function add(string $ipAddress): ?NotAllowedIp;

    public function addFromUser(User $user): ?NotAllowedIp;

    public function addFromSession(Session $session): ?NotAllowedIp;

    public function remove(string $ipAddress): bool;
}
