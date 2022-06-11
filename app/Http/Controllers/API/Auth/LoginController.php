<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\Auth\Login;
use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Services\AuthService;
use App\Services\Google2FAService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Jenssegers\Agent\Agent;

class LoginController extends Controller
{
    protected $authService;

    protected $google2FAService;

    protected $requires2FA = false;

    public function __construct(AuthService $service, Google2FAService $google2FAService)
    {
        $this->authService = $service;
        $this->google2FAService = $google2FAService;

        $this->middleware('ip.not-allowed')->only('login');
        $this->middleware('block.sports-book')->only('login');
    }

    public function login(Request $request): JsonResponse
    {
        $this->validateFields($request, $this->loginRules());

        $failed = ['message' => trans('auth.failed')];
        $invalidOTP = ['message' => trans('auth.2fa.invalid_otp')];
        $OTPDisabled = ['message' => trans('auth.2fa.disabled')];

        try {
            $username = $request->input('username');
            $password = $request->input('password');

            $user = $this->getUserByProvidedUsername($username);

            if (! $user || ! Hash::check($password, $user->getAuthPassword())) {
                return response()->json($failed, 401);
            }

            if ($user->self_x === 1) {
                return response()->json(["message" => trans('user.selfx')], 403);
            }

            if ($user->restricted) {
                return response()->json(["message" => trans('user.restricted')], 403);
            }

            if ($this->requires2FA && ! $user->is2FAEnabled()) {
                return response()->json($OTPDisabled, 400);
            }

            if ($this->requires2FA && $user->is2FAEnabled()) {
                if (is_null($request->one_time_password)) {
                    return response()->json($invalidOTP, 422);
                }

                $OTP = $request->one_time_password;
                $isValid = $this->google2FAService->checkOTP($user->google2fa_secret, $OTP);

                if (!$isValid) {
                    return response()->json($invalidOTP, 422);
                }
            }

            $token = $this->authService->requestAccessToken($user->username, $password);

            return $this->authenticated($request, $user, $token);
        } catch (\Exception $exception) {
            return response()->json($failed, 500);
        }
    }

    protected function validateFields(Request $request, array $rules)
    {
        $request->validate($rules);
    }

    protected function loginRules(): array
    {
        return [
            'username' => 'required|string',
            'password' => 'required|string|min:8|max:28',
            'one_time_password' => $this->requires2FA ? 'required|numeric' : 'nullable|numeric'
        ];
    }

    protected function willAttemptWithEmail(string $username): bool
    {
        return filter_var($username, FILTER_VALIDATE_EMAIL);
    }

    protected function getUserByProvidedUsername(string $username): ?User
    {
        if (! $this->willAttemptWithEmail($username)) {
            return User::where('username', $username)->first();
        }

        return User::where('email', $username)->first();
    }

    protected function authenticated(Request $request, User $user, $token): JsonResponse
    {
        event(new Login($user, new Agent(), $request));

        $data = [
            'token' => $token
        ];

        if (!$user->email_verified_at) {
            $data['user'] = [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
                'email_verified_at' => $user->email_verified_at,
            ];
        } else {
            $data['user'] = [
                'id' => $user->id,
                'email' => $user->email,
                'username' => $user->username,
            ];
        }

        return \response()->json($data);
    }
}
