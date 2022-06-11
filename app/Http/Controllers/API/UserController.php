<?php

namespace App\Http\Controllers\API;

use App\Contracts\Repositories\NotAllowedIpRepository;
use App\Contracts\Repositories\SessionRepository;
use App\Contracts\Repositories\UserRepository;
use App\Events\Auth\EmailUpdated;
use App\Events\Auth\PasswordUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\AccountResource;
use App\Models\Bets\Bet;
use App\Models\Currencies\CryptoCurrency;
use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Users\User;
use Illuminate\Support\Facades\DB;
use App\Notifications\Auth\AccountChanges;
use App\Services\AuthService;
use App\Services\UserService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\User as UserResource;
use App\Models\Casino\Games\CasinoBet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Jenssegers\Agent\Agent;

class UserController extends Controller
{
    protected $service;

    private $repository;

    public const SEARCH_FIELD_USERNAME = 'username';

    public const SEARCH_FIELD_ID = 'id';

    public const SEARCH_FIELD_IP = 'IP';

    private const SEARCH_FIELDS = [
        self::SEARCH_FIELD_USERNAME,
        self::SEARCH_FIELD_ID,
        self::SEARCH_FIELD_IP,
    ];

    public function __construct(UserService $service, UserRepository $repository)
    {
        $this->middleware('auth:api');
        $this->middleware('requires.password')->only('update');

        $this->middleware('role:bookie')->except('update', 'account');

        $this->middleware('permission:list users')->only('index');
        $this->middleware('permission:search users')->only('search');
        $this->middleware('permission:filter users')->only('filter');
        $this->middleware('permission:restrict users')->only('restrict');
        $this->middleware('permission:create not allowed ips')->only('blockIp');
        $this->middleware('permission:delete not allowed ips')->only('blockIp');

        $this->service = $service;
        $this->repository = $repository;
    }

    public function index()
    {
        $relations = [
            'wallets', 'wallets.currency', 'bets',
            'bets.wallet', 'bets.wallet.currency'
        ];

        return UserResource::collection($this->repository->paginate(relations: $relations));
    }

    /**
     * @param Request $request
     * @return AccountResource
     */
    public function account(Request $request)
    {
        $user = $request->user()->load('wallets', 'wallets.currency', 'wallets.address');

        return new AccountResource($user);
    }

    /**
     * @param $value
     * @param $field
     * @param string $filter
     * @return UserResource|JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws ValidationException
     */
    public function search($value, $field, Request $request, $filter = UserRepository::DEFAULT_FILTER)
    {
        $request->validate([
            'status' => 'nullable|in:restricted,unrestricted',
            'per_page' => 'nullable|integer|in:20,50,200'
        ]);
        $this->validateSearch($field);
        $this->validateFilter($filter);

        $result = null;
        $relations = [
            'wallets', 'wallets.currency', 'bets',
            'bets.wallet', 'bets.wallet.currency'
        ];

        $perPage = $request->per_page ?? 20;

        if ($field === self::SEARCH_FIELD_ID) {
            $result = $this->repository->filterById($value, $filter, $relations);
        } elseif ($field === self::SEARCH_FIELD_USERNAME) {
            $result = $this->repository->whereUsernameLike($value, $filter, $relations, $perPage);
        } elseif ($field === self::SEARCH_FIELD_IP) {
            $result = $this->repository->whereIpAddressLike($value, $filter, $relations, $perPage);
        }

        if ($result instanceof LengthAwarePaginator && $result->isNotEmpty()) {
            return UserResource::collection($result);
        }

        return UserResource::collection(collect([]));
    }

    /**
     * @param string $field
     * @throws ValidationException
     */
    protected function validateSearch(string $field)
    {
        $validator = Validator::make(
            [
                'field' => $field
            ],
            [
                'field' => [
                    'required',
                    'string',
                    Rule::in(self::SEARCH_FIELDS)
                ]
            ]
        );

        $messages = $validator->getMessageBag();

        if ($validator->fails()) {
            throw ValidationException::withMessages($messages->toArray());
        }
    }

    /**
     * @param $type
     * @return JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws ValidationException
     */
    public function filter($type, Request $request)
    {
        $request->validate([
            'status' => 'nullable|in:all,restricted,unrestricted',
            'self_status' => 'nullable|integer|in:0,1,10',
            'per_page' => 'nullable|integer|in:20,50,200',
            'search_value' => 'nullable|string',
            'search_field' => 'nullable|string|in:username,email,ip'
        ]);
        
        $this->validateFilter($type);
        
        $relations = [
            'wallets', 'wallets.currency', 'bets',
            'bets.wallet', 'bets.wallet.currency'
        ];
        
        $perPage = $request->per_page ?? 20;

        $users = UserResource::collection(
            $this->repository->filter(
                type: $type,
                restricted: $request->status ? $request->status === 'restricted' : null,
                searchValue: $request->search_value,
                searchField: $request->search_field,
                selfStatus: $request->self_status,
                perPage: $perPage,
                relations: $relations
            )
        );
        
        return $users;
    }

    /**
     * @param string $filter
     * @throws ValidationException
     */
    protected function validateFilter(string $filter)
    {
        $validator = Validator::make(
            [
                'filter' => $filter
            ],
            [
                'filter' => [
                    'required',
                    'string',
                    Rule::in(UserRepository::FILTERS)
                ]
            ]
        );

        $messages = $validator->getMessageBag();

        if ($validator->fails()) {
            throw ValidationException::withMessages($messages->toArray());
        }
    }

    /**
     * @param $id
     * @param Request $request
     * @param AuthService $authService
     * @return JsonResponse|UserResource
     */
    public function restrict($id, Request $request, AuthService $authService)
    {
        $this->validateStatusUpdate($request);

        $user = $this->repository->findById($id, true);

        $user->setStatus($request->input('restrict'));

        if (!$user->save()) {
            return response()->json(['message' => trans('user.update.failed')], 500);
        }

        if ($request->input('restrict')) {
            $authService->revokeUserTokens($user);
        }

        $user->refresh();
        $user->load(['wallets', 'wallets.currency']);

        return new UserResource($user);
    }

    public function validateStatusUpdate(Request $request)
    {
        $request->validate([
            'restrict' => 'required|boolean'
        ]);
    }

    public function blockIp(User $user, Request $request, NotAllowedIpRepository $ipRepository, AuthService $auth)
    {
        $block = $request->input('block');

        $response = null;

        $ipAddress = $user->getAttribute('ip_address');

        if (!$block) {
            $removed = $ipRepository->remove($ipAddress);

            return response()->json([
                'message' => $removed ?
                    "The {$ipAddress} was remove from blacklist" :
                    "Unable to remove {$ipAddress} from blacklist"
            ]);
        }

        if ($ipRepository->addFromUser($user)) {
            $auth->revokeSessionTokenByIp($ipAddress);

            return response()->json(['message' => "{$ipAddress} added to the blacklist"]);
        }

        return response()->json(['message' => "Unable to add {$ipAddress} to the blacklist"]);
    }

    public function update(UpdateRequest $request): JsonResponse
    {
        $user = $request->user();

        $failed = [
            'message' => trans('user.update.failed')
        ];
        $uniqueEmail = [
            'message' => trans('validation.unique', ['attribute' => 'email']),
        ];

        try {
            $attrs = [
                'ip_address' => $request->ip(),
            ];

            if ($request->new_password) {
                $attrs['password'] = $request->new_password;
            }

            if ($request->email) {
                $attrs['email'] = $request->email;
            }

            if (!$this->service->update($user, $attrs)) {
                return response()->json($failed, 422);
            }

            $user->notify(new AccountChanges($request->ip()));

            $user->refresh();

            $this->logActions($request, $user);

            return response()->json([
                'message' => trans('user.update.success'),
                'user' => $user
            ]);
        } catch (QueryException | \Exception $queryException) {
            if ($queryException->getCode() === "23000") {
                return response()->json($uniqueEmail, 422);
            }

            return response()->json($failed, 500);
        }
    }

    private function logActions(Request $request, User $user): void
    {
        if ($request->input('new_password')) {
            event(new PasswordUpdated($user, new Agent(), $request));
        }

        if ($request->input('email')) {
            event(new EmailUpdated($user, new Agent(), $request));
        }
    }

    public function profile(User $user)
    {
        $relations = [
            'wallets', 'wallets.currency', 'bets',
            'bets.wallet', 'bets.wallet.currency'
        ];

        $btcCasinoAvg = 0;
        $usdtCasinoAvg = 0;
        $casinoBtcPL = 0;
        $casinoUsdtPL = 0;

        $user = $this->repository->withProfitLoss($user->getKey())->with($relations)->find($user->getKey());

        $btcWallet = $user->wallets->first(fn ($w) => $w->currency->ticker === CryptoCurrency::TICKER_BTC);
        $usdtWallet = $user->wallets->first(fn ($w) => $w->currency->ticker === CryptoCurrency::TICKER_USDT);

        $btcBets = $user->bets->filter(fn ($b) => $b->wallet_id === $btcWallet->getKey());
        $usdtBets = $user->bets->filter(fn ($b) => $b->wallet_id === $usdtWallet->getKey());

        $btcAverageStake = $btcBets->avg(fn ($b) => $b->stake);
        $usdtAverageStake = $usdtBets->avg(fn ($b) => $b->stake);
        $eurAverageStake = ($btcWallet->currency->eur_price * $btcAverageStake) + ($usdtWallet->currency->eur_price * $usdtAverageStake);

        $casino = $this->getCasinoData($user->id);
        
        if ($casino) 
        {
            $casinoBtcPL = $this->calculateCPL($casino, 'BTC');
            $casinoUsdtPL = $this->calculateCPL($casino, 'USDT');
            $btcCasinoAvg = $this->calculateAvg($casino->btcTotal, $casino->btc_bet_count);
            $usdtCasinoAvg = $this->calculateAvg($casino->usdtTotal, $casino->usd_bet_count);
        }

        return [
            'id' => $user->getKey(),
            'username' => $user->username,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'notes' => $user->notes,
            'balance' => [
                'BTC' => $btcWallet->balance,
                'USDT' => $usdtWallet->balance,
                'EUR' => $btcWallet->eurBalance + $usdtWallet->eurBalance,
            ],
            'profit_loss' => [
                'sports' => [
                    'BTC' => $user->btc_profit_loss,
                    'USDT' => $user->usdt_profit_loss,
                    'EUR' => ($btcWallet->currency->eur_price * $user->btc_profit_loss) + ($usdtWallet->currency->eur_price * $user->usdt_profit_loss),
                ],
                'casino' => [
                    'BTC' => $casinoBtcPL,
                    'USDT' => $casinoUsdtPL,
                    'EUR' => ($btcWallet->currency->eur_price * $casinoBtcPL),
                ],
            ],
            'total_bets' => [
                'sports' => [
                    'BTC' => $btcBets->count(),
                    'USDT' => $usdtBets->count(),
                ],
                'casino' => [
                    'BTC' => isset($casino->btc_bet_count) ? $casino->btc_bet_count : 0,
                    'USDT' => isset($casino->usd_bet_count) ? $casino->usd_bet_count : 0,
                ],
            ],
            'average_stakes' => [
                'sports' => [
                    'BTC' => $btcAverageStake,
                    'USDT' => $usdtAverageStake,
                    'EUR' => $eurAverageStake,
                ],
                'casino' => [
                    'BTC' => $btcCasinoAvg,
                    'USDT' => $usdtCasinoAvg,
                    'EUR' => ($btcWallet->currency->eur_price * $btcCasinoAvg) + ($usdtWallet->currency->eur_price * $usdtCasinoAvg),
                ],
            ],
            'btc' => $btcWallet,
            'usdt' => $usdtWallet
        ];
    }

    public function calculateAvg($total, $bet_count)
    {
        if ($total > 0 && $bet_count > 0) {
            return ($total / $bet_count);
        } else {
            return 0;
        }
    }

    public function getCasinoData($userId)
    {
        $query = CasinoBet::query();

        $query->select('game_uuid', 'player_id');

        $query->withCount(['betCountForUser as usd_bet_count' => function ($q) {
            $q->select(DB::raw('coalesce(COUNT(game_uuid),0)'));
            $q->where('crypto_currency', 'USDT');
        }]);
        $query->withCount(['betCountForUser as btc_bet_count' => function ($q) {
            $q->select(DB::raw('coalesce(COUNT(game_uuid),0)'));
            $q->where('crypto_currency', 'BTC');
        }]);

        $query->withCount(['winCountForUser as btcWinTotal' => function ($q) {
            $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
            $q->where('crypto_currency', 'BTC');
        }]);
        $query->withCount(['winCountForUser as usdtWinTotal' => function ($q) {
            $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
            $q->where('crypto_currency', 'USDT');
        }]);

        $query->withCount(['betCountForUser as usdtTotal' => function ($q) {
            $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
            $q->where('crypto_currency', 'USDT');
        }]);
        $query->withCount(['betCountForUser as btcTotal' => function ($q) {
            $q->select(DB::raw('coalesce(SUM(crypto_amt),0)'));
            $q->where('crypto_currency', 'BTC');
        }]);

        $query->where('type', 'bet');
        $query->where('rollback', 0);
        $query->whereNull('status');

        if (is_array($userId)) {
            $query->whereIn('player_id', $userId);
            return $query->groupBy('player_id')->get();
        } else {
            $query->where('player_id', $userId);
            return $query->groupBy('player_id')->first();
        }

    }

    public function calculateCPL($response, $cur)
    {
        if ($cur === 'BTC') {
            return ($response->btcWinTotal - $response->btcTotal);
        } else {
            return ($response->usdtWinTotal - $response->usdtTotal);
        }       
    }

    public function updateNotes(User $user, Request $request)
    {
        $request->validate([
            'notes' => 'string|max:255'
        ]);

        $user->notes = $request->notes;
        $user->save();

        return [];
    }

    public function switchUser ($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return 'User Not Found';
            }

            $user->type = 1;
            $user->save();

            return 'User switched to Test Mode';
        } catch (\Throwable $th) {
            return $th->getMessage();
        }
    }
}
