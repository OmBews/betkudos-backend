<?php

namespace App\Repositories;

use App\Contracts\Repositories\SessionRepository as SessionRepositoryContract;
use App\Models\Sessions\Session;
use App\Contracts\Repositories\UserRepository;
use App\Models\Users\Devices\Device;
use App\Models\Users\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SessionRepository extends Repository implements SessionRepositoryContract
{
    public function __construct(Session $session)
    {
        parent::__construct($session);
    }

    public function createNew(User $user, Device $device, string $tokenId, string $ipAddress): Session
    {
        $attrs = [
            'oauth_access_token_id' => $tokenId,
            'user_id' => $user->getKey(),
            'device_id' => $device->getKey(),
            'ip_address' => $ipAddress,
        ];

        return $this->create($attrs);
    }

    public function filter(string $filter = self::DEFAULT_FILTER, int $userStatus = null)
    {
        if ($filter === self::TODAY_SESSIONS_FILTER) {
            if ($userStatus != null) {
                return $this->fromToday($perPage = Session::PER_PAGE, $userStatus);
            } else {
                return $this->fromTodayWithoutFilter();
            }
        } else {
            if ($userStatus != null) {
                return $this->fromTodayAll($perPage = Session::PER_PAGE, $userStatus);
            } else {
                return $this->fromTodayWithoutFilterAll();
            }
        }

        return $this->paginate();
    }

    public function fromTodayWithoutFilter($perPage = Session::PER_PAGE)
    {
        return $this->model
                    ->newQuery()
                    ->where('created_at', '>', now()->subDay())
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    public function fromTodayWithoutFilterAll($perPage = Session::PER_PAGE)
    {
        return $this->model
                    ->newQuery()
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    public function fromToday($perPage = Session::PER_PAGE, $userStatus)
    {
        $query = $this->model
                    ->newQuery();
        
        if ($userStatus == 2) {
            $query->whereHas('getByUserRestrictionFilter', function ($query) {
                $query->whereIn('restricted', [0,1]);
            });    
        } else {
            $query->whereHas('getByUserRestrictionFilter', function ($query) use ($userStatus) {
                $query->where('restricted', $userStatus);
            });
        }
        
        return $query->where('created_at', '>', now()->subDay())
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    public function fromTodayAll($perPage = Session::PER_PAGE, $userStatus)
    {
        $query = $this->model
                    ->newQuery();
        
        if ($userStatus == 2) {
            $query->whereHas('getByUserRestrictionFilter', function ($query) {
                $query->whereIn('restricted', [0,1]);
            });    
        } else {
            $query->whereHas('getByUserRestrictionFilter', function ($query) use ($userStatus) {
                $query->where('restricted', $userStatus);
            });
        }
        
        return $query->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function filterByUserId(int $userId, string $filter = self::DEFAULT_FILTER)
    {
        if ($filter === self::TODAY_SESSIONS_FILTER) {
            return $this->fromTodayByUserId($userId);
        }

        return $this->findByUserId($userId);
    }


    public function fromTodayByUserId(int $userId, $perPage = Session::PER_PAGE)
    {
        return $this->model
                    ->where('user_id', $userId)
                    ->where('created_at', '>', now()->subDay())
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    public function findByUserId(int $userId, $perPage = Session::PER_PAGE)
    {
        return $this->model
                    ->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    public function filterByUsername(string $username, string $filter = self::DEFAULT_FILTER)
    {
        $userRepository = app()->make(UserRepository::class);

        $users = $userRepository->whereUsernameLike($username);

        if ($users instanceof LengthAwarePaginator) {
            return $this->filterManyUsers(collect($users->items()), $filter);
        }

        return null;
    }

    public function filterByIpAddress(string $ipAddress, string $filter = self::DEFAULT_FILTER)
    {
        if ($filter === self::TODAY_SESSIONS_FILTER) {
            return $this->fromTodayByIpAddress($ipAddress, true);
        }

        return $this->findByIpAddress($ipAddress, true);
    }

    public function fromTodayByIpAddress(string $ipAddress, bool $like = false, $perPage = Session::PER_PAGE)
    {
        $query = $this->model->newQuery();

        $query = $like ?
                 $query->where('ip_address', 'LIKE', '%' . $ipAddress . '%') :
                 $query->where('ip_address', $ipAddress);

        return $query->where('created_at', '>', now()->subDay())
                     ->orderBy('created_at', 'DESC')
                     ->paginate($perPage);
    }

    public function findByIpAddress(string $ipAddress, bool $like = false, $paginate = true, $page = Session::PER_PAGE)
    {
        $query = $this->model->newQuery();

        $query = $like ?
                 $query->where('ip_address', 'LIKE', '%' . $ipAddress . '%') :
                 $query->where('ip_address', $ipAddress);

        $query = $query->orderBy('created_at', 'DESC');

        if (!$paginate && $like) {
            return $query->get();
        }

        return !$paginate ? $query->first() : $query->paginate($page);
    }

    public function findByOauthAccessTokenId(string $oauthAccessTokenId)
    {
        return $this->model
                    ->newQuery()
                    ->where('oauth_access_token_id', $oauthAccessTokenId)
                    ->first();
    }

    public function paginate($perPage = Session::PER_PAGE)
    {
        return $this->model
                    ->newQuery()
                    ->orderBy('created_at', 'DESC')
                    ->paginate($perPage);
    }

    public function filterManyUsers(Collection $users, string $filter = self::DEFAULT_FILTER)
    {
        if ($filter === self::TODAY_SESSIONS_FILTER) {
            return $this->fromTodayWhereIn($users);
        }

        return $this->whereIn($users);
    }

    public function fromTodayWhereIn(Collection $users)
    {
        return $this->model
                    ->newQuery()
                    ->where('created_at', '>', now()->subDay())
                    ->whereIn('user_id', $users->pluck('id')->toArray())
                    ->paginate(Session::PER_PAGE);
    }

    public function whereIn(Collection $users)
    {
        return $this->model
                    ->newQuery()
                    ->whereIn('user_id', $users->pluck('id')->toArray())
                    ->paginate(Session::PER_PAGE);
    }

    public function whereIpAddress(string $ipAddress): Collection
    {
        return $this->newQuery()
                    ->where('ip_address', $ipAddress)
                    ->get();
    }

    public function whereUserId(int $id): Collection
    {
        return $this->newQuery()
                    ->where('user_id', $id)
                    ->get();
    }
}
