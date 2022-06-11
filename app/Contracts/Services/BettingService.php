<?php

namespace App\Contracts\Services;

use App\Exceptions\Betting\InvalidMatchException;
use App\Exceptions\Betting\MarketNotFoundException;
use App\Exceptions\Betting\NoBetPlaced;
use App\Exceptions\Betting\OddsHasChangedException;
use App\Exceptions\Betting\OddsNotFoundException;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use Carbon\Carbon;
use Illuminate\Support\Collection;

interface BettingService
{
    /**
     * @param array $matches
     * @param int|float|null $multipleStake
     * @throws \Throwable
     * @throws NoBetPlaced
     * @throws InvalidMatchException
     * @throws MarketNotFoundException
     * @throws OddsHasChangedException
     * @throws OddsNotFoundException
     * @return void
     *
     * Validates the selections and throws an exception if any selection is wrong
     *
     */
    public function validate(array $matches, Wallet $wallet, $multipleStake = null);

    /**
     * @param User $user
     * @param array $singles
     * @param array $multiples
     * @param int|float|null $multipleStake
     * @throws NoBetPlaced
     * @return Collection
     *
     * Place bets for the given validated selections
     *
     */
    public function placeBets(User $user, array $singles, array $multiples, $multipleStake = null): Collection;

    /**
     * @param array $selections
     * @return bool
     *
     * Check if there are live matches for the given selections
     */
    public function selectionsHasLiveMatches(array $selections): bool;

    /**
     * @param User $user
     * @param array $singles
     * @param array $multiples
     * @return array
     *
     * Creates a new delayed bet and returns the time when it should be sent again
     * and the hash based on the user and their selections
     */
    public function delay(User $user, array $singles, array $multiples): array;

    /**
     * @param User $user
     * @param string $hash
     * @return Carbon|null
     *
     * Returns the Carbon instance if the delay was store for the given hash
     */
    public function getBetDelay(User $user, string $hash): ?Carbon;

    /**
     * @param User $user
     * @param string $uniqueId
     * @return string
     *
     * Builds the key to store the delay on Cache
     */
    public function delayedBetKey(User $user, string $uniqueId): string;

    /**
     * @param User $user
     * @param string $hash
     * @param array $selections
     * @return bool
     *
     * Validates the hash by comparing the given selections with the hash sent by the user
     */
    public function validateDelayHash(User $user, string $hash, array $selections): bool;

    /**
     * @param User $user
     * @param string $hash
     * @return bool
     *
     * Check if the delay get expired on the Cache
     */
    public function isDelayedBetExpired(User $user, string $hash): bool;

    /**
     * @param User $user
     * @param string $hash
     * @return bool
     *
     * Ensure the user waited for the delay before try to place the same bet again
     */
    public function userDelayedTheBet(User $user, string $hash): bool;

    /**
     * @param User $user
     * @param string $uniqueId
     * @return bool
     *
     * Clear the delay cache key
     */
    public function forgetBetDelay(User $user, string $uniqueId): bool;

    /**
     * @param array $selections
     * @return array
     *
     * Transform the incoming selections to the data needed on DB
     */
    public function prepareSelections(array $selections): array;
}
