<?php

namespace App\Services;

use App\Contracts\Services\CasinoServiceInterface;
use App\Models\Casino\Games\Game;
use App\Models\Casino\Games\GameCategory;
use App\Models\Casino\Games\GameSession;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use App\Slotegrator\AggregatorRequest;
use App\Slotegrator\Slotegrator;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use App\Models\Casino\Games\CasinoBet;
use App\Models\Casino\Games\FreeSpin;
use App\Models\Casino\Games\CasinoRefund;
use App\Models\Casino\Games\CasinoWin;
use App\Models\Casino\Games\CasinoRollback;
use App\Models\Casino\Providers\Provider;
use Exception;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\PseudoTypes\True_;

use function PHPUnit\Framework\throwException;

class CasinoService implements CasinoServiceInterface
{
    public function __construct(private Slotegrator $slotegrator)
    {
    }

    /*
    |--------------------------------------------------------------------------
    | To get list of games for game category page
    |--------------------------------------------------------------------------
    */
    public function filter(string $category = null, string $providers = null, string $search = null, int $userId): LengthAwarePaginator
    {
        try {
            $query = Game::query();

            if ($category != 'provider') {
                if ($category === 'Favourites') {
                    $query->whereHas('favourite', function ($query) use ($userId) {
                        $query->where('user_id', $userId);
                    });
                } else {
                    $query->where('category', $category);
                }
            }

            // To filter according multiple provider
            if ($providers) {
                $providers = explode(',', $providers);
                if (count($providers)) {
                    $query->whereIn('provider', $providers);
                }
            }

            if ($search) {
                $query->where('name', 'LIKE', '%' . $search . '%');
            }

            // To donot show games of the provider which is blocked
            $query->whereHas('gameProvider', function ($query) {
                $query->where('status', 0);
            });
            
            return $query->where('is_active', true)
                ->where('is_mobile', false)
                ->whereNotNull('has_currency')
                ->orderBy('provider')
                ->paginate(18, ['id', 'provider', 'name', 'image', 'type', 'has_currency', 'category']);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To get list of games for lobby section
    |--------------------------------------------------------------------------
    */
    public function lobby(int $userId, bool $mobile = false): Collection
    {
        try {
            $categories = GameCategory::all(['name']);
            $lobby = collect();

            foreach ($categories as $category) {
                $provider = Game::query();

                if ($category->name === 'Favourites') {
                    $provider->whereHas('provider');
                } else {
                    $provider->where('category', $category->name);
                }

                $providers = $provider->where('is_active', true)
                    ->where('is_mobile', $mobile)
                    ->whereNotNull('has_currency')
                    ->groupBy('provider')
                    ->orderBy('provider')
                    ->get(['provider']);

                $query = Game::query();
                $query->whereDoesntHave('favourite', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                });
                $query->where('category', $category->name);
                
                // To donot show games of the provider which is blocked
                $query->whereHas('gameProvider', function ($query) {
                    $query->where('status', 0);
                });
                
                $games = $query->where('is_active', true)
                    ->where('is_mobile', $mobile)
                    ->whereNotNull('has_currency')
                    ->orderBy('name')
                    ->limit(15)
                    ->get(['id', 'provider', 'name', 'image', 'type', 'has_currency', 'category']);

                $category->setRelation('games', $games);
                $category->setRelation('providers', $providers);
                $lobby->add($category);
            }
            return $lobby;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function lobbysearch(string $search = null, int $userId, bool $mobile = false): Collection
    {
        $query = Game::query();
        $query->where('name', 'like', '%' . $search . '%');
        $query->whereHas('gameProvider', function ($query) {
            $query->where('status', 0);
        });

        return $query->where('is_active', true)
            ->where('is_mobile', $mobile)
            ->whereNotNull('has_currency')
            ->orderBy('name')
            ->limit(18)
            ->get(['id', 'provider', 'name', 'image', 'type', 'has_currency', 'category']);
    }

    /**
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function init(Game $game, User $user, $walletId, $currency, bool $demo = false): string
    {
        if ($demo) {
            $gameInitDemoResponse = $this->slotegrator->gameInitDemo($game->aggregator_uuid);
            return $gameInitDemoResponse->throw()->object()->url;
        }

        $lobbyData = [];

        if ($game->has_lobby) {
            $response = $this->slotegrator->gameLobby($game->aggregator_uuid, $currency);
            $lobbyData = $response->throw()->object();
            $lobby = Arr::first($lobbyData->lobby);

            $lobbyData = [
                'lobby_data' => $lobby->lobbyData
            ];
        }

        $balance = $user->wallet->exchangeBalance($currency);
        $exchangeValue = $user->wallet->exchangeValue($currency);

        $session = $this->newSession($game, $user, $exchangeValue, $walletId, $currency);

        $gameInitResponse = $this->slotegrator->gameInit(
            $game->aggregator_uuid,
            $user->getKey(),
            $user->username,
            $currency,
            $session->getKey(),
            $lobbyData
        );

        $sessionId = $session->getKey();

        dispatch(function () use ($balance, $sessionId) {
            $response = (new Slotegrator())->balanceNotify($sessionId, $balance);
        });

        return $gameInitResponse->throw()->object()->url;
    }


    /*
    |--------------------------------------------------------------------------
    | To self validate to get production key from slotgrator, It may harm data stored in Bet, Win, Refund model
    |--------------------------------------------------------------------------
    */
    public function selfValidate()
    {
        return $this->slotegrator->validate();
    }

    /*
    |--------------------------------------------------------------------------
    | To create session key for casino game
    |--------------------------------------------------------------------------
    */
    private function newSession(Game $game, User $user, $balance, $walletId, $currency): GameSession
    {
        $session = new GameSession();

        $session->user_id = $user->getKey();
        $session->game_id = $game->getKey();
        $session->exchangeRate = $balance;
        $session->wallet = $walletId;
        $session->currency = $currency;

        $session->save();
        return $session;
    }

    /*
    |--------------------------------------------------------------------------
    | Aggregator call for user balance
    |--------------------------------------------------------------------------
    */
    public function balance(AggregatorRequest $request): array
    {
        $activeWallet = GameSession::where('user_id', $request->player_id)->orderBy('id', 'DESC')->first();
        $wallet = Wallet::find($activeWallet->wallet);

        if (!$wallet) {
            return [
                'error_code' => 'INTERNAL_ERROR',
                'error_description' => 'Player not found.'
            ];
        }

        $balance = ($wallet->balance * $activeWallet->exchangeRate); // $wallet->exchangeBalance($request->currency);

        return [
            'balance' => number_format($balance, 4)
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | To deduct balance form user wallet used in bet request
    |--------------------------------------------------------------------------
    */
    public function deductBalanceFromWallet($amount, $sessionKey)
    {
        try {

            $session = GameSession::find($sessionKey);

            $wallet = Wallet::find($session->wallet);

            if ($session->exchangeRate > 1) {
                $betConvAmtount = ($amount / $session->exchangeRate);
                $wallet->balance = ($wallet->balance - $betConvAmtount);
            } else {
                $betConvAmtount = ($amount * $session->exchangeRate);
                $wallet->balance = ($wallet->balance - $betConvAmtount);
            }

            $wallet->save();
            return number_format(($wallet->balance * $session->exchangeRate), 4);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | To add win or refund amount to user wallet
    |--------------------------------------------------------------------------
    */
    public function addBalanceToWallet($amount, $sessionKey)
    {
        try {
            $session = GameSession::find($sessionKey);

            $wallet = Wallet::find($session->wallet);

            if ($session->exchangeRate > 1) {
                $betConvAmtount = ($amount / $session->exchangeRate);
                $wallet->balance = ($wallet->balance + $betConvAmtount);
            } else {
                $betConvAmtount = ($amount * $session->exchangeRate);
                $wallet->balance = ($wallet->balance + $betConvAmtount);
            }

            $wallet->save();

            return number_format(($wallet->balance * $session->exchangeRate), 4);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | To get user balance
    |--------------------------------------------------------------------------
    */
    public function getUserBalance($sessionKey)
    {
        try {
            $session = GameSession::find($sessionKey);
            $wallet = Wallet::find($session->wallet);

            return number_format(($wallet->balance * $session->exchangeRate), 4);
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To set and get bet id in game session model
    |--------------------------------------------------------------------------
    */
    public function setBetId($sessionKey, $betId)
    {
        try {
            $session = GameSession::find($sessionKey);
            $session->bet_id = $betId;
            $session->save();
            return  True;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getBetId($sessionKey)
    {
        try {
            $session = GameSession::find($sessionKey);
            if ($session)
                return $session->bet_id;
            else
                throw new Exception("Getting Error", 1);
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function updateCryptoBetAmount($sessionId, $betId, $amount)
    {
        $bet = CasinoBet::find($betId);
        $session = GameSession::with('getWallet.currency')->find($sessionId);

        if ($session->exchangeRate > 1) {
            $bet->crypto_amt = ($amount / $session->exchangeRate);
            if ($session->currency !== 'EUR')
                $bet->euro_amt = ($amount / $session->getWallet->currency->eur_price);
            else
                $bet->euro_amt = $amount;
        } else {
            $bet->crypto_amt = ($amount * $session->exchangeRate);
            if ($session->currency !== 'EUR')
                $bet->euro_amt = ($amount * $session->getWallet->currency->eur_price);
            else
                $bet->euro_amt = $amount;
        }

        $bet->crypto_currency = $session->getWallet->currency->ticker;
        $bet->save();

        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | Aggregator Bet request
    | Bet with provided **transaction_id** should be processed only once. If you already processedthis transaction, then return successful
    | response with processed transaction ID on the integrator side.
    |--------------------------------------------------------------------------
    */
    public function placeBet(AggregatorRequest $request): array
    {
        try {

            $checkBet = CasinoBet::where('transaction_id', $request->transaction_id)->first(); // Check if bet transaction id already exist.
            $updatedWalletAmount = $this->getUserBalance($request->session_id); // Get exchanged balance

            if ($checkBet) {
                return [
                    'balance' => $updatedWalletAmount,
                    'transaction_id' => $checkBet->id
                ];
            }

            if (!$this->checkExceedAmount($request->session_id, $request->amount)) {
                return [
                    'error_code' => 'INTERNAL_ERROR',
                    'error_description' => 'Bet Price Exceeded'
                ];
            }

            $bet = new CasinoBet();
            $bet->amount = $request->amount;
            $bet->currency = $request->currency;
            $bet->game_uuid = $request->game_uuid;
            $bet->player_id = $request->player_id;
            $bet->transaction_id = $request->transaction_id;
            $bet->session_id = $request->session_id;
            $bet->type = $request->type; // "bet", "tip", "freespin"
            $bet->freespin_id = $request->freespin_id ? $request->freespin_id : "";
            $bet->quantity = $request->quantity ? $request->quantity : 0;
            $bet->round_id = $request->round_id ? $request->round_id : '';
            $bet->finished = $request->finished ? $request->finished : 0;
            $bet->save();

            $this->updateCryptoBetAmount($request->session_id, $bet->id, $request->amount); //Update crypto amount to bet
            $this->setBetId($request->session_id, $bet->id); // Update Bet Id in game session model

            if ($request->type === "bet") {
                $updatedWalletAmount = $this->deductBalanceFromWallet($request->amount, $request->session_id); // Deduct the right balance from wallet
            }

            // To chekc it is a bet or freespin
            if ($request->type === "freespin") {

                // Free spin Bet
                $freeSpinBet = $this->slotegrator->freeSpinBet($request->game_uuid, $request->currency);

                // Free spin set
                $freeSpinSet = $this->slotegrator->freeSpinSet(
                    $request->player_id,
                    $request->player_id,
                    $request->currency,
                    $request->quantity,
                    date("Y-m-d h:i:s"),
                    date("Y-m-d h:i:s", strtotime('+6 hours')),
                    $request->freespin_id,
                    $request->game_uuid,
                    $request->game_uuid,
                    $freeSpinBet->total_bets
                );

                // Freespin get
                $freeSpinGet = $this->slotegrator->freeSpinGet($request->freespin_id);

                // Store Free spin content in FreeSpin model
                $this->storeFreeSpin($freeSpinGet);
            }

            return [
                'balance' => $updatedWalletAmount,
                'transaction_id' => $bet->id
            ];
        } catch (\Throwable $th) {
            return [
                'error_code' => 'INTERNAL_ERROR',
                'error_description' => 'Place Bet request error' . $th
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Aggregator win request
    | Win with provided **transaction_id** should be processed only once. If you already processedthis transaction, then return successful
    | response with processed transaction ID on the integrator side.
    |--------------------------------------------------------------------------
    */
    public function updateCryptoWinAmount($sessionId, $betId, $amount)
    {
        $win = CasinoWin::find($betId);
        $session = GameSession::with('getWallet.currency')->find($sessionId);

        if ($session->exchangeRate > 1) {
            $win->crypto_amt = ($amount / $session->exchangeRate);
            if ($session->currency !== 'EUR')
                $win->euro_amt = ($amount / $session->getWallet->currency->eur_price);
            else
                $win->euro_amt = $amount;
        } else {
            $win->crypto_amt = ($amount * $session->exchangeRate);
            if ($session->currency !== 'EUR')
                $win->euro_amt = ($amount * $session->getWallet->currency->eur_price);
            else
                $win->euro_amt = $amount;
        }

        $win->crypto_currency = $session->getWallet->currency->ticker;
        $win->save();

        return true;
    }

    public function win(AggregatorRequest $request): array
    {
        try {

            $checkWin = CasinoWin::where('transaction_id', $request->transaction_id)->first(); // Check if win transaction id already exist.
            if ($checkWin) {
                $userBalance = $this->getUserBalance($request->session_id); // Get balance according exchange rate
                return [
                    'balance' => $userBalance,
                    'transaction_id' => $checkWin->id
                ];
            }

            $win = new CasinoWin();
            $win->amount = $request->amount; // win amount
            $win->currency = $request->currency;
            $win->game_uuid = $request->game_uuid;
            $win->player_id = $request->player_id;
            $win->transaction_id = $request->transaction_id;
            $win->bet_id = $this->getBetId($request->session_id);
            $win->type = $request->type; // It should be 'win', 'Jackpot' and 'freespin'
            $win->session_id = $request->session_id; // session id from integrator side
            $win->freespin_id = $request->freespin_id; // free spin id
            $win->quantity = $request->quantity ? $request->quantity : 0; // freespin quantity
            $win->round_id = $request->round_id ? $request->round_id : ''; //current transaction round
            $win->finished = $request->finished ? $request->finished : 0; //is round is finished in game
            $win->save();

            if ($request->amount > 0) {
                $updatedWalletAmount = $this->addBalanceToWallet($request->amount, $request->session_id); // Win amount added to wallet
                $this->updateCryptoWinAmount($request->session_id, $win->id, $request->amount); //Update crypto amount to win
                
            } else {
                $updatedWalletAmount = $this->getUserBalance($request->session_id); // Get balance according exchange rate
            }

            return [
                'balance' => $updatedWalletAmount,
                'transaction_id' => $win->id
            ];
        } catch (\Throwable $th) {
            return [
                'error_code' => 'INTERNAL_ERROR',
                'error_description' => 'Error in win request. ' . $th->getMessage()
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Aggregator refund request
    | Bet with provided **bet_transaction_id** should be refunded processed only once. If you already refundedthis transaction, then in
    | response return processed refund transaction ID on the integrator side.
    |--------------------------------------------------------------------------
    */
    public function refund(AggregatorRequest $request): array
    {
        try {
            $checkRefund = CasinoRefund::where('bet_transaction_id', $request->bet_transaction_id)->first();
            $userBalance = $this->getUserBalance($request->session_id);
            if ($checkRefund) {
                return [
                    'balance' => $userBalance,
                    'transaction_id' => $checkRefund->id
                ];
            }

            $bet = CasinoBet::where('transaction_id', $request->bet_transaction_id)->first();
            if ($bet) {
                $bet->status = "refund";
                $bet->save();
                $betId = $bet->id;
                $balanceRefund = $this->addBalanceToWallet($request->amount, $request->session_id);
            } else {
                $balanceRefund = $this->getUserBalance($request->session_id);
            }

            $refund = new CasinoRefund();
            $refund->amount = $request->amount;
            $refund->transaction_id = $request->transaction_id;
            $refund->bet_id = isset($betId) ? $betId : 0;
            $refund->bet_transaction_id = $request->bet_transaction_id; // Game Transaction Id to be refuncded
            $refund->session_id = $request->session_id; // session id from integrator side
            $refund->freespin_id = $request->freespin_id;
            $refund->quantity = $request->quantity ? $request->quantity : 0;
            $refund->round_id = $request->round_id ? $request->round_id : '';
            $refund->finished = $request->finished ? $request->finished : 0;
            $refund->save();

            return [
                'balance' => $balanceRefund,
                'transaction_id' => $refund->id
            ];
        } catch (\Exception $e) {
            return [
                'error_code' => 'INTERNAL_ERROR',
                'error_description' => 'Refund not initiated, ' . $e->getMessage()
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Aggregator rollback request
    | If some transaction will be missed Game Aggregator will accept rollback response as failed.
    | All transactions specified in rollback request should be refunded processed only once. If you already processedsome transaction, then
    | transaction_id should be in response as successfully processed.
    |--------------------------------------------------------------------------
    */
    public function rollback(AggregatorRequest $request): array
    {
        try {

            $checkTransaction = CasinoRollback::where('transaction_id', $request->transaction_id)->first();
            if ($checkTransaction) {
                return [
                    'balance' => $this->getUserBalance($request->session_id),
                    'transaction_id' => $checkTransaction->id,
                    'rollback_transactions' => $checkTransaction->rollback_transactions
                ];
            }

            // Save rolledback transaction
            $rollback = new CasinoRollback();
            $rollback->currency = $request->currency;
            $rollback->game_uuid = $request->game_uuid;
            $rollback->player_id = $request->player_id;
            $rollback->transaction_id = $request->transaction_id; // Game aggregator side
            $rollback->rollback_transactions = json_encode($request->rollback_transactions); // List of round transaction in array
            $rollback->provider_round_id = $request->provider_round_id; //Game aggregator round id
            $rollback->round_id = $request->round_id ? $request->round_id : '';
            $rollback->type = $request->type; // "rollback"
            $rollback->session_id = $request->session_id; //integrator session id
            $rollback->save();

            // Complete rollback functionality
            if (count($request->rollback_transactions) > 0) {
                foreach ($request->rollback_transactions as $value) {
                    if ($value['action'] === 'win') {
                        $this->rollbackWinTransaction($value['amount'], $value['transaction_id'], $value['type'], $request->session_id);
                    }
                    if ($value['action'] === 'bet') {
                        $this->rollbackBetTransaction($value['amount'], $value['transaction_id'], $value['type'], $request->session_id);
                    }
                }
            }

            return [
                'balance' => $this->getUserBalance($request->session_id),
                'transaction_id' => $rollback->id,
                'rollback_transactions' => $request->rollback_transactions // all transaction id
            ];
        } catch (\Throwable $th) {
            return [
                'error_code' => 'INTERNAL_ERROR',
                'error_description' => $th
            ];
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Rollback BET transactions
    |--------------------------------------------------------------------------
    */
    public function rollbackBetTransaction($amount, $transactionId, $type, $sessionId)
    {
        try {
            $bet = CasinoBet::where('transaction_id', $transactionId)->where('type', $type)->first();
            if ($bet) {
                $bet->rollback = 1;
                $bet->save();
                if ($amount > 0) {
                    $this->addBalanceToWallet($amount, $sessionId); // Need to reverse bet amount
                }
            }

            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Rollback WIN transactions
    |--------------------------------------------------------------------------
    */
    public function rollbackWinTransaction($amount, $transactionId, $type, $sessionId)
    {
        try {
            $win = CasinoWin::where('transaction_id', $transactionId)->where('type', $type)->first();
            if ($win) {
                $win->rollback = 1;
                $win->save();

                if ($amount > 0) {
                    $this->deductBalanceFromWallet($amount, $sessionId); // Need to reverse bet amount
                }
            }
            return true;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * To chekc exceeded bet amount
     */
    public function checkExceedAmount($sessionKey, $amount)
    {
        $userBalance = $this->getUserBalance($sessionKey);
        if (number_format($amount) <= number_format($userBalance)) {
            return true;
        } else {
            return false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Store Free Spin content
    |--------------------------------------------------------------------------
    */
    public function storeFreeSpin($response)
    {
        try {
            $fspin = new FreeSpin();
            $fspin->player_id = $response->player_id;
            $fspin->currency = $response->currency;
            $fspin->quantity = $response->quantity;
            $fspin->quantity_left = $response->quantity_left;
            $fspin->valid_from = $response->valid_from;
            $fspin->valid_until = $response->valid_until;
            $fspin->freespin_id = $response->freespin_id;
            $fspin->bet_id = $response->bet_id;
            $fspin->total_bet_id = $response->total_bet_id;
            $fspin->denomination = $response->denomination;
            $fspin->game_uuid = $response->game_uuid;
            $fspin->status = $response->status;
            $fspin->is_canceled = $response->is_canceled;
            $fspin->total_win = $response->total_win;
            $fspin->save();
            return true;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
