<?php

namespace App\Contracts\Services;


use App\Models\Casino\Games\Game;
use App\Models\Users\User;
use App\Slotegrator\AggregatorRequest;
use App\Slotegrator\Slotegrator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CasinoServiceInterface
{
    public function __construct(Slotegrator $slotegrator);

    public function filter(string $category = null, string $providers = null, string $search = null, int $userId): LengthAwarePaginator;

    public function lobby(int $userId, bool $mobile = false): Collection;

    public function lobbysearch(string $search = null, int $userId, bool $mobile = false): Collection;

    public function init(Game $game, User $user, $walletId, $currency, bool $demo = false);

    public function selfValidate();

    public function balance(AggregatorRequest $request): array;

    public function placeBet(AggregatorRequest $request): array;

    public function win(AggregatorRequest $request): array;

    public function refund(AggregatorRequest $request): array;

    public function rollback(AggregatorRequest $request): array;
}
