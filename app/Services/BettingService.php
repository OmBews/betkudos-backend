<?php

namespace App\Services;

use App\Contracts\Services\BettingService as BettingServiceContract;
use App\Exceptions\Betting\AsianHandicapOnMultipleException;
use App\Exceptions\Betting\InvalidMatchException;
use App\Exceptions\Betting\MarketNotFoundException;
use App\Exceptions\Betting\NoBetPlaced;
use App\Exceptions\Betting\OddsHasChangedException;
use App\Exceptions\Betting\OddsNotFoundException;
use App\Exceptions\Betting\ProfitOverLimitException;
use App\Exceptions\Betting\SelectionSuspendedException;
use App\Exceptions\Betting\UnavailableSelectionException;
use App\Exceptions\Betting\WalletNotFoundException;
use App\Models\Bets\Bet;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Event;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class BettingService implements BettingServiceContract
{
    /**
     * @inheritDoc
     */
    public function placeBets(User $user, array $singles = [], array $multiples = [], $multipleStake = null): Collection
    {
        if (!$user->wallet) {
            throw new WalletNotFoundException();
        }

        $bets = collect();

        $user->wallet->balance -= $this->sumStakes($singles, $multipleStake);

        if (is_numeric($multipleStake) && count($multiples)) {
            $bets->add($this->placeMultiple($user, $multiples, (float) $multipleStake));
        }

        if (count($singles)) {
            foreach ($this->placeSingles($user, $singles) as $single) {
                $bets->add($single);
            }
        }

        if ($bets->count() && $user->wallet->save()) {
            return $bets;
        }

        throw new NoBetPlaced(trans('bets.no_bets'));
    }

    public function prepareSelections(array $selections): array
    {
        $ids = array_map(fn($selection) => $selection['odd_id'], $selections);
        $odds = MarketOdd::query()->whereIn('id', $ids)->get();

        $mapper = function ($selection) use($odds) {
            $odd = $odds->where('id', $selection['odd_id'])->first();

            if (!$odd) {
                throw new OddsNotFoundException();
            }

            return array_merge(
                $selection,
                [
                    'name' => $odd->name,
                    'header' => $odd->header,
                    'handicap' => $odd->handicap,
                ]
            );
        };

        return array_map($mapper, $selections);
    }

    public function delay(User $user, array $singles, array $multiples): array
    {
        // Creates a unique id to this operation
        $betUniqueId = $this->uniqueId($user, array_merge($singles, $multiples));
        // Use strtotime('+5 seconds') to define when the FE should request again
        $this->storeBetDelay($user, $betUniqueId);

        return [
           'betUniqueId' => $betUniqueId,
           'wait' => 5000
        ];
    }

    private function uniqueId(User $user, array $selections): string
    {
        $handler = fn ($item) => $item['match_id']."_".$item['odd_id'];

        $ids = array_map($handler, $selections);

        return hash('sha256', "user_{$user->getKey()}_wallet{$user->wallet->getKey()}_".implode('_', $ids));
    }

    private function storeBetDelay(User $user, string $uniqueId): void
    {
        $delay = now()->addSeconds(5);

        Cache::put($this->delayedBetKey($user, $uniqueId), $delay, 10);
    }

    public function getBetDelay(User $user, string $hash): ?Carbon
    {
        return Cache::get($this->delayedBetKey($user, $hash));
    }

    public function forgetBetDelay(User $user, string $uniqueId): bool
    {
        return Cache::forget($this->delayedBetKey($user, $uniqueId));
    }

    public function delayedBetKey(User $user, string $uniqueId): string
    {
        return "{$user->getKey()}_".$uniqueId."_delay";
    }

    public function validateDelayHash(User $user, string $hash, array $selections): bool
    {
        return hash_equals($this->uniqueId($user, $selections), $hash);
    }

    public function isDelayedBetExpired(User $user, string $hash): bool
    {
        return $this->getBetDelay($user, $hash) === null;
    }

    public function userDelayedTheBet(User $user, string $hash): bool
    {
        $delay = $this->getBetDelay($user, $hash);

        return  $delay !== null && now()->greaterThanOrEqualTo($delay);
    }

    /**
     * @param User $user
     * @param array $matches
     * @param int|float $stake
     * @return Bet|Model
     */
    private function placeMultiple(User $user, array $matches, $stake): Bet
    {
        $stake = (float) $stake;
        $isLive = $this->selectionsHasLiveMatches($matches);
        $profit = $this->getProfit($matches, $stake);

        $bet = new Bet();

        $bet->fill([
            'user_id' => $user->getKey(),
            'type' => Bet::TYPE_MULTIPLE,
            'stake' => $stake,
            'live' => $isLive,
            'profit' => $profit,
            'code' => Bet::genUniqueCode(),
            'wallet_id' => $user->wallet->getKey(),
            'ip_address' => request()->ip(),
        ]);

        $bet->save();

        $selections = [];

        foreach ($matches as $match) {
            $selections[] = $this->makeSelection($match);
        }

        $bet->selections()->saveMany($selections);
        $bet->load($this->relations());

        return $bet;
    }

    private function placeSingles(User $user, array $singles): array
    {
        $bets = [];

        foreach ($singles as $single) {
            $stake = (float) $single['stake'];

            $bet = new Bet();
            $bet->fill([
                'user_id' => $user->getKey(),
                'type' => Bet::TYPE_SINGLE,
                'stake' => $stake,
                'live' => $this->selectionsHasLiveMatches([$single]),
                'profit' => $this->getProfit([$single], $stake),
                'code' => Bet::genUniqueCode(),
                'wallet_id' => $user->wallet->getKey(),
                'ip_address' => request()->ip(),
            ])->save();

            $bet->selections()->save($this->makeSelection($single));

            $bet->load($this->relations());

            $bets[] = $bet;
        }

        return $bets;
    }

    private function relations(): array
    {
        return [
            'wallet', 'wallet.currency', 'selections',
            'selections.marketOdd', 'selections.market', 'selections.match',
            'selections.match.home', 'selections.match.away'
        ];
    }

    private function makeSelection(array $selection)
    {
        return BetSelection::query()->make([
            'match_id' => $selection['match_id'],
            'market_id' => $selection['market_id'],
            'odd_id' => $selection['odd_id'],
            'odds' => $selection['odds'],
            'name' => $selection['name'],
            'header' => $selection['header'],
            'handicap' => $selection['handicap'],
        ]);
    }

    /**
     * @param array $singles
     * @param int|float $stake
     * @return float|int
     */
    private function getProfit(array $singles, $stake)
    {
        $accumulator = 0;

        foreach ($singles as $single) {
            if ($accumulator === 0) {
                $accumulator = (float) $single['odds'];
            } else {
                $accumulator *= (float) $single['odds'];
            }
        }

        return $accumulator * $stake;
    }

    /**
     * @inheritDoc
     */
    public function validate(array $matches, Wallet $wallet, $multipleStake = null)
    {
        $profitLimit = $wallet->currency->max_bet_profit;
        $profits = [];

        if ($multipleStake) {
            $profits[] = $this->getProfit($matches, $multipleStake);
        }

        foreach ($matches as $match) {
            $matchId = $match['match_id'];
            $marketId = $match['market_id'];
            $selectionId = $match['odd_id'];
            $odds = $match['odds'];

            if (! $event = $this->findMatch($matchId)) {
                throw new InvalidMatchException();
            } elseif ($this->marketDoesNotExists($marketId)) {
                throw new MarketNotFoundException();
            } elseif ($this->oddsDoesNotExists($matchId, $marketId, $selectionId)) {
                throw new OddsNotFoundException();
            }

            $selection = MarketOdd::query()
                ->where('id', $selectionId)
                ->where('match_id', $matchId)
                ->where('market_id', $marketId)
                ->first();

            if ($selection->odds <> $odds) {
                throw new OddsHasChangedException();
            } elseif ($selection->is_suspended) {
                throw new SelectionSuspendedException();
            } elseif ($event->isLive() && !$selection->is_live) {
                throw new UnavailableSelectionException();
            } elseif ($event->isNotStarted() && $selection->is_live) {
                throw new UnavailableSelectionException();
            } elseif ($multipleStake && $this->isAsianHandicapMarket($marketId)) {
                throw new AsianHandicapOnMultipleException();
            }

            if ($this->isSingle($match)) {
                $profits[] = $this->getProfit([$match], $match['stake']);
            }
        }

        foreach ($profits as $profit) {
            if ($profit > $profitLimit) {
                $message = trans('bets.profit_over_limit', [
                    'profit' => round($profit, 8),
                    'limit' => $profitLimit,
                    'currency' => $wallet->currency->ticker
                ]);

                throw new ProfitOverLimitException($message);
            }
        }
    }

    /**
     * @param int $matchId
     * @return \Illuminate\Database\Eloquent\Builder|Event|Model|object|null
     */
    protected function findMatch(int $matchId)
    {
        return Event::query()
                ->whereKey($matchId)
                ->whereIn('time_status', [Event::STATUS_NOT_STARTED, Event::STATUS_IN_PLAY])
                ->first(['id', 'time_status']);
    }

    protected function marketDoesNotExists(int $marketId)
    {
        return Market::query()->find($marketId, ['id']) === null;
    }

    protected function oddsDoesNotExists(int $matchId, int $marketId, int $oddId)
    {
        return MarketOdd::query()
                        ->where('id', $oddId)
                        ->where('match_id', $matchId)
                        ->where('market_id', $marketId)
                        ->first() === null;
    }

    protected function isAsianHandicapMarket(int $marketId)
    {
        $market = Market::query()->whereKey($marketId)->first();

        return $market->key === 'asian_handicap';
    }

    /**
     * @param array $matches
     * @param int|float $multipleStake
     * @return int|float
     */
    protected function sumStakes(array $matches, $multipleStake = 0)
    {
        $sumStakes = function ($accumulator, $match) {
            $accumulator += $match['stake'] ?? 0;

            return $accumulator;
        };

        return array_reduce(
            $matches,
            $sumStakes,
            $multipleStake
        );
    }

    public function selectionsHasLiveMatches(array $selections): bool
    {
        $query = Event::query();

        $query->where(function ($query) use ($selections) {
            foreach ($selections as $single) {
                $query->orWhere('id', $single['match_id'])
                    ->where('time_status', Event::STATUS_IN_PLAY);
            }
        });

        return $query->get(['id', 'time_status'])->count() > 0;
    }

    private function isSingle(array $match): bool
    {
        return isset($match['stake']) && is_numeric($match['stake']);
    }
}
