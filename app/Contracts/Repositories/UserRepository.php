<?php

namespace App\Contracts\Repositories;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use phpDocumentor\Reflection\Types\Self_;

interface UserRepository
{
    public const FILTER_TOP_WINNERS = 'TOP_WINNERS';

    public const FILTER_TOP_LOSERS = 'TOP_LOSERS';

    public const FILTER_ALL_USERS = 'ALL_USERS';

    public const FILTER_STATUS = 'STATUS';

    public const DEFAULT_FILTER = self::FILTER_ALL_USERS;

    public const FILTERS = [
        self::FILTER_TOP_WINNERS,
        self::FILTER_TOP_LOSERS,
        self::FILTER_ALL_USERS,
        self::FILTER_STATUS,
    ];

    public function paginate($perPage = User::PER_PAGE, array $relations = []);

    public function filterById(int $userId, string $filter = self::DEFAULT_FILTER, array $relations = []);

    public function findById(int $userId, bool $fail = false): ?User;

    public function findByUsername(string $username);

    public function whereUsernameLike(string $username, string $filter = self::DEFAULT_FILTER, array $relations = [], $perPage = User::PER_PAGE);

    public function whereIpAddressLike(string $ipAddress, string $filter = self::DEFAULT_FILTER, array $relations = [], $perPage = User::PER_PAGE);

    public function findByEmail(string $email);

    public function whereStatus(bool $restricted, array $relations = [], $perPage = User::PER_PAGE);

    public function losers($perPage = User::PER_PAGE, array $relations = []);

    public function winners($perPage = User::PER_PAGE, array $relations = []);

    public function filter(
        string $type = self::DEFAULT_FILTER,
        bool $restricted = false,
        string $searchValue = null,
        string $searchField = null,
        int $perPage = 20,
        array $relations = [],
        int $selfStatus = null,
    );

    public function withProfitLoss(int $userId = null): Builder;
}
