<?php

namespace App\Http\Controllers\API\Auth;

use App\Http\Controllers\Controller;
use App\Models\Users\User;
use App\Services\Google2FAService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Password;
use Jenssegers\Agent\Agent;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    private $google2FAService;

    private Agent $agent;

    private const DISABLED_2FA = 'passwords.2fa_disabled';

    public function __construct(Google2FAService $google2FAService)
    {
        $this->google2FAService = $google2FAService;
        $this->agent = new Agent();
    }

    public function reset(Request $request, $method = 'email')
    {
        if ($method === '2FA') {
            return $this->resetWith2FA($request);
        }

        $request->validate($this->rules(), $this->validationErrorMessages());

        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $this->broker()->reset(
            $this->credentials($request),
            function ($user, $password) {
                $this->resetPassword($user, $password);
            }
        );

        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == Password::PASSWORD_RESET
            ? $this->sendResetResponse($request, $response)
            : $this->sendResetFailedResponse($request, $response);
    }

    public function resetWith2FA(Request $request): JsonResponse
    {
        $request->validate($this->google2FARules(), []);

        if (! $user = $this->getUser($request)) {
            return $this->sendReset2FAFailedResponse(Password::INVALID_USER, 404);
        }

        if (! $user->is2FAEnabled()) {
            return $this->sendReset2FAFailedResponse(self::DISABLED_2FA, 403);
        }

        $secret = $user->google2fa_secret;
        $OTP = $request->one_time_password;

        if (! $this->google2FAService->checkOTP($secret, $OTP)) {
            return $this->sendReset2FAFailedResponse(Password::INVALID_TOKEN, 400);
        }

        $this->resetPassword($user, $request->password);

        return $this->sendReset2FAResponse(Password::PASSWORD_RESET);
    }

    /**
     * @return array
     */
    private function google2FARules(): array
    {
        return [
            'one_time_password' => 'required|integer',
            'email' => 'required|email',
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'max:28'
            ],
        ];
    }

    /**
     * @param Request $request
     * @return mixed
     */
    private function getUser(Request $request)
    {
        return User::where('email', $request->email)->first();
    }

    /**
     * @param string $response
     * @return JsonResponse
     */
    private function sendReset2FAResponse(string $response): JsonResponse
    {
        return response()->json([ 'message' => trans($response) ]);
    }

    /**
     * @param string $response
     * @param int $status
     * @return JsonResponse
     */
    private function sendReset2FAFailedResponse(string $response, $status = 500): JsonResponse
    {
        return response()->json([ 'message' => trans($response) ], $status);
    }

    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json([ 'message' => trans($response) ]);
    }

    protected function sendResetFailedResponse(Request $request, $response)
    {
        $status = 500;

        if ($response === 'passwords.token') {
            $status = 400;
        } elseif ($response === 'passwords.user') {
            $status = 404;
        } elseif ($response === 'passwords.throttled') {
            $status = 429;
        }

        return response()->json([ 'message' => trans($response) ], $status);
    }

    /**
     * Reset the given user's password.
     *
     * @param User $user
     * @param string $password
     * @return void
     */
    protected function resetPassword(User $user, $password)
    {
        $user->password = $password;

        $user->save();

        event(new PasswordReset($user));
    }

    protected function redirectTo()
    {
        return $this->agent->isPhone() || $this->agent->isTablet() ?
            config('app.frontend_url') :
            config('app.frontend_desktop');
    }
}
