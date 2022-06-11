<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepository as UserRepositoryContract;
use App\Models\Bets\Bet;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Self_;

class UserRepository extends Repository implements UserRepositoryContract
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function paginate($perPage = User::PER_PAGE, array $relations = [])
    {
        return $this->withProfitLoss()->with($relations)->paginate($perPage);
    }

    public function findByUsername(string $username)
    {
        return $this->withProfitLoss()->where('username', $username)->first();
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function whereUsernameLike(string $username, string $filter = self::DEFAULT_FILTER, array $relations = [], $perPage = User::PER_PAGE)
    {
        $query = $this->withProfitLoss()->where('username', 'LIKE', '%' . $username . '%');

        if ($filter === self::FILTER_STATUS) {
            $query = $query->orderBy('restricted', 'DESC');
        }

        return $query->with($relations)->paginate($perPage);
    }

    public function whereIpAddressLike(string $ipAddress, string $filter = self::DEFAULT_FILTER, array $relations = [], $perPage = User::PER_PAGE)
    {
        $query = $this->withProfitLoss()->where('ip_address', 'LIKE', '%' . $ipAddress . '%');

        if ($filter === self::FILTER_STATUS) {
            $query = $query->orderBy('restricted', 'DESC');
        }

        return $query->with($relations)->paginate($perPage);
    }

    public function whereStatus(bool $restricted, array $relations = [], $perPage = User::PER_PAGE)
    {
        return $this->withProfitLoss()
            ->where('restricted', $restricted)
            ->with($relations)
            ->paginate($perPage);
    }

    public function filterById(int $userId, string $filter = self::DEFAULT_FILTER, array $relations = [])
    {
        $query = $this->withProfitLoss()->where('id', $userId);

        return $query->with($relations)->paginate(User::PER_PAGE);
    }

    public function findById(int $userId, bool $fail = false): ?User
    {
        if ($fail) {
            return $this->model->findOrFail($userId);
        }

        return $this->model->find($userId);
    }

    public function losers($perPage = User::PER_PAGE, array $relations = [])
    {
        return $this->withProfitLoss()->with($relations)->orderByRaw('profit_loss is null')->orderBy('profit_loss')->paginate($perPage);
    }

    public function winners($perPage = User::PER_PAGE, array $relations = [])
    {
        return $this->withProfitLoss()->with($relations)->orderByDesc('profit_loss')->paginate($perPage);
    }

    public function withProfitLoss(int $userId = null): Builder
    {
        $btc = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first();
        $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first();

        $id = $userId ?? "`users`.`id`";

        $query = User::query()->selectRaw("
            *,
            (
                select sum(`crypto_amt`) as aggregate
                from
                    `casino_bets`
                where `casino_bets`.`player_id` = $id
                and `status` is NULL
                and `rollback` = 0
                and `crypto_currency` = 'BTC'
            ) as `casino_total_staked`,
            (
                select (casino_total_staked) * $btc->eur_price as aggregate
            ) as `casino_eur_staked`,
            (
                select sum(`crypto_amt`) as aggregate
                from
                    `casino_wins`
                where `casino_wins`.`player_id` = $id
                and `rollback` = 0
                and `crypto_currency` = 'BTC'
            ) as `casino_total_earned`,
            (
                select (casino_total_staked - casino_total_earned) as aggregate
            ) as `casino_profit_loss`,
            (
                select (casino_total_staked - casino_total_earned) * $btc->eur_price as aggregate
            ) as `casino_eur_profit_loss`,
            (
                select sum(`stake`) as aggregate
                from
                    `bets`
                where `bets`.`user_id` = $id
                and `status` in ('half_lost', 'lost')
                and exists (
                    select * from `wallets`
                    where
                        `bets`.`wallet_id` = `wallets`.`id`
                    and `crypto_currency_id` = 1
                )
            ) as `btc_total_staked`,

            (
                select sum(`profit`) as aggregate
                from
                    `bets`
                where `bets`.`user_id` = $id
                and `status` in ('half_won', 'won')
                and exists (
                    select * from `wallets`
                    where
                        `bets`.`wallet_id` = `wallets`.`id`
                    and `crypto_currency_id` = 1
                )
            ) as `btc_total_earned`,

            (
                select (btc_total_staked - btc_total_earned) as aggregate
            ) as `btc_profit_loss`,

            (
                select (btc_total_staked - btc_total_earned) * $btc->eur_price as aggregate
            ) as `btc_eur_profit_loss`,

            (
                select sum(`stake`) as aggregate
                from
                    `bets`
                where `bets`.`user_id` = $id
                and `status` in ('half_lost', 'lost')
                and exists (
                    select * from `wallets`
                    where
                        `bets`.`wallet_id` = `wallets`.`id`
                    and `crypto_currency_id` = 2
                )
            ) as `usdt_total_staked`,

            (
                select sum(`profit`) as aggregate
                from
                    `bets`
                where `bets`.`user_id` = $id
                and `status` in ('half_won', 'won')
                and exists (
                    select * from `wallets`
                    where
                        `bets`.`wallet_id` = `wallets`.`id`
                    and `crypto_currency_id` = 2
                )
            ) as `usdt_total_earned`,

            (
                select (usdt_total_staked - usdt_total_earned) as aggregate
            ) as `usdt_profit_loss`,

            (
                select (usdt_total_staked - usdt_total_earned) * $usdt->eur_price as aggregate
            ) as `usdt_eur_profit_loss`,

            (
                select usdt_eur_profit_loss + btc_eur_profit_loss as aggregate
            ) as `profit_loss`
        ");


        return $query;
    }

    public function filter(
        string $type = self::DEFAULT_FILTER,
        bool $restricted = null,
        string $searchValue = null,
        string $searchField = null,
        int $perPage = 20,
        array $relations = [],
        int $selfStatus = null
    ): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $query = $this->withProfitLoss();
        
        if (! is_null($restricted)) {
            $query->where('restricted', $restricted);
        }

        if ($searchField && $searchValue) {
            if ($searchField === 'username') {
                $query->where('username', 'LIKE', '%'.$searchValue.'%');
            } elseif ($searchField === 'ip') {
                $query->where('ip_address', 'LIKE', '%'.$searchValue.'%');
            } elseif ($searchField === 'email') {
                $query->where('email', 'LIKE', '%'.$searchValue.'%');
            }
        }

        if ($selfStatus) {
            if ($selfStatus == 10) {
                $query->whereIn('self_x', [0,1]);
            } else {
                $query->where('self_x', $selfStatus);
            }
        }
        

        if ($type === self::FILTER_TOP_LOSERS) {
            $query->orderByRaw('profit_loss is null')->orderBy('profit_loss');
        } elseif ($type === self::FILTER_TOP_WINNERS) {
            $query->orderByDesc('profit_loss');
        }

        return $query->with($relations)->paginate($perPage);
    }
}
