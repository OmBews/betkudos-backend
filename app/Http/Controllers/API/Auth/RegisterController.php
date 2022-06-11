<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\Auth\Registered;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Users\User;
use App\Notifications\Auth\EmailVerification;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Jenssegers\Agent\Agent;

class RegisterController extends Controller
{
    private $userService;
    private $authService;

    public function __construct(UserService $userService, AuthService $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;

        $this->middleware('ip.not-allowed')->only('register');
        $this->middleware('block.sports-book')->only('register');
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $attrs = [
            'username' => $username = $request->input('username'),
            'password' => $password = $request->input('password'),
            'email' => $email = $request->input('email'),
            'ip_address' => $request->ip(),
        ];

        $failed = [ 'message' => trans('user.register.failed') ];

        try {
            $user = $this->userService->create($attrs);
        } catch (\Exception $e) {
            return response()->json($failed, 500);
        }

        try {
            $cryptoCurrencies = CryptoCurrency::query()->whereIn('ticker', [CryptoCurrency::TICKER_BTC, CryptoCurrency::TICKER_USDT])->get();

            $authToken = $this->authService->requestAccessToken($username, $password);
            $data = [
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'username' => $user->username,
                    'email_verified_at' => $user->email_verified_at,
                ],
                'token' => $authToken
            ];
            $user->notify(new EmailVerification($request->product));
            event(new Registered($user, new Agent(), $request));

            $walletService = new WalletService();

            foreach ($cryptoCurrencies as $cryptoCurrency) {
                $walletService->createWallet($user, $cryptoCurrency);
            }


            $response = response()->json($data, 201);
        } catch (\Exception $e) {
            if ($user instanceof User) {
                $user->delete();
                $user->wallets()->delete();
            }

            $response = response()->json($failed, 500);
        }

        return $response;
    }
}
