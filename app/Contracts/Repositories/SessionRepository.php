<?php

namespace App\Contracts\Repositories;

use App\Models\Sessions\Session;
use App\Models\Users\Devices\Device;
use App\Models\Users\User;
use Illuminate\Support\Collection;

interface SessionRepository
{
    public const ALL_SESSIONS_FILTER = 'ALL';

    public const TODAY_SESSIONS_FILTER = 'TODAY_SESSIONS';

    public const DEFAULT_FILTER = self::TODAY_SESSIONS_FILTER;

    public function __construct(Session $session);

    public function createNew(User $user, Device $device, string $tokenId, string $ipAddress): Session;

    public function filter(string $filter = self::DEFAULT_FILTER, int $userStatus = null);

    public function filterByUserId(int $userId, string $filter = self::DEFAULT_FILTER);

    public function filterByUsername(string $username, string $filter = self::DEFAULT_FILTER);

    public function filterByIpAddress(string $ipAddress, string $filter = self::DEFAULT_FILTER);

    public function findByUserId(int $userId, $perPage = Session::PER_PAGE);

    public function findByIpAddress(string $ipAddress, bool $like = false, $paginate = true, $page = Session::PER_PAGE);

    public function whereIpAddress(string $ipAddress): Collection;

    public function whereUserId(int $id): Collection;

    public function findByOauthAccessTokenId(string $oauthAccessTokenId);

    public function fromTodayByUserId(int $userId, $perPage = Session::PER_PAGE);

    public function fromToday($perPage = Session::PER_PAGE, $userStatus);

    public function fromTodayByIpAddress(string $ipAddress, bool $like = false, $perPage = Session::PER_PAGE);

    public function filterManyUsers(Collection $users, string $filter = self::DEFAULT_FILTER);

    public function fromTodayWhereIn(Collection $users);

    public function whereIn(Collection $users);

    public function paginate($perPage = Session::PER_PAGE);
}
