<?php

namespace App\Repositories;

use App\Contracts\Repositories\NotAllowedIpRepository as NotAllowedIpRepositoryContract;
use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Sessions\Session;
use App\Models\Users\User;

class NotAllowedIpRepository extends Repository implements NotAllowedIpRepositoryContract
{
    public function __construct(NotAllowedIp $model)
    {
        parent::__construct($model);
    }

    public function find(string $ipAddress): ?NotAllowedIp
    {
        return $this->model
                    ->where('ip_address', $ipAddress)
                    ->first();
    }

    public function add(string $ipAddress): ?NotAllowedIp
    {
        $notAllowedIp = $this->find($ipAddress);

        if ($notAllowedIp) {
            return $notAllowedIp;
        }

        $attrs = [
            'ip_address' => $ipAddress
        ];

        return $this->model->create($attrs);
    }

    public function addFromUser(User $user): ?NotAllowedIp
    {
        return $this->add(
            $user->getAttribute('ip_address')
        );
    }

    public function addFromSession(Session $session): ?NotAllowedIp
    {
        return $this->add(
            $session->getAttribute('ip_address')
        );
    }

    public function remove(string $ipAddress): bool
    {
        $notAllowedIp = $this->find($ipAddress);

        if (! $notAllowedIp) {
            return true;
        }

        return $notAllowedIp->delete();
    }
}
