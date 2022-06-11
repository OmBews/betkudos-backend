<?php

namespace App\Repositories;

use App\Models\Sessions\Logs\SessionLog;
use App\Contracts\Repositories\SessionLogRepository as ClientSessionRepositoryContract;

class SessionLogRepository extends Repository implements ClientSessionRepositoryContract
{
    public function __construct(SessionLog $model)
    {
        parent::__construct($model);
    }

    public function findByUserId(int $userId, string $filter = self::DEFAULT_FILTER)
    {
        if ($filter === self::TODAY_FILTER) {
            return $this->todaySessionsByUserId($userId);
        }

        return $this->model
                      ->where('user_id', $userId)
                      ->orderBy('created_at', 'DESC')
                      ->paginate(SessionLog::PER_PAGE);
    }

    public function todaySessionsByUserId(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->where('created_at', '>', now()->subDay())
            ->orderBy('created_at', 'DESC')
            ->paginate(SessionLog::PER_PAGE);
    }

    public function findByIpAddress(string $ipAddress, string $filter = self::DEFAULT_FILTER)
    {
        if ($filter === self::TODAY_FILTER) {
            return $this->todaySessionsByIpAddress($ipAddress);
        }

        return $this->model
            ->where('ip_address', $ipAddress)
            ->orderBy('created_at', 'DESC')
            ->paginate(SessionLog::PER_PAGE);
    }

    public function todaySessionsByIpAddress(string $ipAddress)
    {
        return $this->model
            ->where('ip_address', $ipAddress)
            ->where('created_at', '>', now()->subDay())
            ->orderBy('created_at', 'DESC')
            ->paginate(SessionLog::PER_PAGE);
    }

    public function logIn(int $sessionId)
    {
        return $this->model
              ->where('session_id', $sessionId)
              ->where('action', SessionLog::ACTION_LOGIN)
              ->orderBy('created_at', 'DESC')
              ->first();
    }

    public function logOut(int $sessionId)
    {
        return $this->model
              ->where('session_id', $sessionId)
              ->where('action', SessionLog::ACTION_LOGOUT)
              ->orderBy('created_at', 'DESC')
              ->first();
    }

    public function getLogInsAndLogOutsInPast24Hours(int $userId)
    {
        return $this->model
            ->where('user_id', $userId)
            ->whereIn('action', [SessionLog::ACTION_LOGIN, SessionLog::ACTION_LOGOUT])
            ->where('created_at', '>', now()->subDay())
            ->orderBy('created_at', 'DESC')
            ->get();
    }
}
