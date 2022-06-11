<?php

namespace App\Http\Controllers\API;

use App\Contracts\Services\BettingService;
use App\Exceptions\Betting\BetExpiredException;
use App\Exceptions\Betting\DelayedBetSentTooEarlyException;
use App\Exceptions\Betting\NoBetPlaced;
use App\Exceptions\Betting\WalletNotFoundException;
use App\Exceptions\Betting\WrongBetHashException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Bets\PlaceBet;
use App\Http\Resources\BetResource;
use App\Models\Users\User;
use App\Jobs\Matches\ProcessLiveOdds;
use App\Jobs\Matches\ProcessResults;
use App\Models\Bets\Bet;
use App\Models\Events\Event;
use App\Models\Wallets\Wallet;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;

class BetController extends Controller
{
    public function __construct()
    {
        $this->middleware('is_not_restricted')->only('placeBet');
    }

    /**
     * @param PlaceBet $request
     * @return JsonResource|AnonymousResourceCollection
     * @throws \Throwable
     */
    public function placeBet(PlaceBet $request, BettingService $bettingService)
    {
        try {
            $singles = $request->singles ?? [];
            $multiples = $request->multiples ?? [];
            $multipleStake = $request->multipleStake ?? null;

            /**
             * @var User $user
             **/
            $user = $request->user();
            $wallet = Wallet::query()->where('user_id', $user->getKey())->whereKey($request->walletId)->first();

            $user->setWallet($wallet);

            if (!$user->wallet) {
                throw new WalletNotFoundException();
            }

            $bettingService->validate($singles, $wallet);
            $bettingService->validate($multiples, $wallet, $multipleStake);

            $singles = $bettingService->prepareSelections($singles);
            $multiples = $bettingService->prepareSelections($multiples);

            $selections = array_merge($singles, $multiples);

            $betUniqueId = $request->betUniqueId;

            if ($bettingService->selectionsHasLiveMatches($selections)) {
                if (!$betUniqueId) {
                    $events = array_map(fn ($event) => $event['match_id'], $selections);
                    $events = array_unique($events);

                    $events = Event::query()->whereIn('id', $events)->with([
                        'sport', 'result', 'liveMarkets',
                        'sport.liveMarkets', 'league', 'home',
                        'away'
                    ])->get();

                    $events->each(function (Event $event) {
                        ProcessLiveOdds::dispatchAfterResponse($event);
                        ProcessResults::dispatchAfterResponse($event);
                    });

                    return new JsonResource($bettingService->delay($user, $singles, $multiples));
                }

                if (!$bettingService->validateDelayHash($user, $betUniqueId, $selections)) {
                    throw new WrongBetHashException();
                }

                if ($bettingService->isDelayedBetExpired($user, $betUniqueId)) {
                    throw new BetExpiredException();
                }

                if (!$bettingService->userDelayedTheBet($user, $betUniqueId)) {
                    throw new DelayedBetSentTooEarlyException();
                }
            }

            DB::beginTransaction(5);

            $bets = $bettingService->placeBets(
                $user,
                $singles,
                $multiples,
                $multipleStake
            );

            DB::commit();

            return BetResource::collection($bets);
        } catch (\Throwable $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function open(Request $request)
    {
        $request->validate(['wallet_id' => 'required|integer|exists:wallets,id']);
        $user = $request->user();
        $wallet = Wallet::query()->findOrFail($request->get('wallet_id'));

        $bets = $user->bets()
            ->where('status', Bet::STATUS_OPEN)
            ->where('wallet_id', $wallet->getKey())
            ->orderBy('created_at', 'DESC')
            ->with($this->relations())
            ->paginate(8);

        return BetResource::collection($bets);
    }

    public function settled(Request $request)
    {
        $request->validate(['wallet_id' => 'required|integer|exists:wallets,id']);
        $user = $request->user();
        $wallet = Wallet::query()->findOrFail($request->get('wallet_id'));

        $bets = $user->bets()
            ->where('status', '!=', Bet::STATUS_OPEN)
            ->where('wallet_id', $wallet->getKey())
            ->orderBy('created_at', 'DESC')
            ->with($this->relations())
            ->paginate(8);

        return BetResource::collection($bets);
    }

    private function relations()
    {
        return [
            'wallet', 'wallet.currency', 'selections',
            'selections.match', 'selections.match.home', 'selections.match.away',
            'selections.marketOdd', 'selections.market'
        ];
    }
}
