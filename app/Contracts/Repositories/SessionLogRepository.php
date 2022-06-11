<?php

namespace App\Contracts\Repositories;

interface SessionLogRepository
{
    public const DEFAULT_FILTER = 'all';

    public const TODAY_FILTER = 'today_sessions';

    public function findByUserId(int $userId, string $filter);

    public function todaySessionsByUserId(int $userId);

    public function findByIpAddress(string $ipAddress, string $filter);

    public function todaySessionsByIpAddress(string $ipAddress);

    public function logIn(int $sessionId);

    public function logOut(int $sessionId);

    public function getLogInsAndLogOutsInPast24Hours(int $userId);
}
