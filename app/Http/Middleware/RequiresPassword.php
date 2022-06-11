<?php

namespace App\Http\Middleware;

use App\Services\Google2FAService;
use Closure;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RequiresPassword
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @return mixed
     * @throws BindingResolutionException
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user->is2FAEnabled()) {
            return $this->handle2FA($request, $next);
        }

        $this->validatePassword($request);

        if (! Hash::check($request->password, $user->password)) {
            return $this->reject();
        }

        return $next($request);
    }

    /**
     * @param Request $request
     * @param Closure $next
     * @return JsonResponse|mixed
     * @throws BindingResolutionException
     */
    public function handle2FA(Request $request, Closure $next)
    {
        $this->validateOTP($request);

        $user = $request->user();
        $OTP = $request->one_time_password;

        if (! $OTP) {
            return $this->reject(true);
        }

        $service = app()->make(Google2FAService::class);

        $isValid = $service->checkOTP($user->google2fa_secret, $OTP);

        if (! $isValid) {
            return $this->reject(true);
        }

        return $next($request);
    }

    /**
     * @param bool $OTP
     * @return JsonResponse
     */
    protected function reject(bool $OTP = false): JsonResponse
    {
        $message = $OTP ?
                trans('auth.2fa.invalid_otp') :
                trans('auth.failed');

        $data = [
            'message' => $message
        ];

        return response()->json($data, 401);
    }

    protected function validatePassword(Request $request)
    {
        $request->validate(['password' => 'required|string']);
    }

    protected function validateOTP(Request $request)
    {
        $request->validate(['one_time_password' => 'required|numeric']);
    }
}
