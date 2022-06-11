<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\Auth\Google2faDisabled;
use App\Events\Auth\Google2faEnabled;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OTPRequest;
use App\Services\Google2FAService;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Jenssegers\Agent\Agent;

class Google2FAController extends Controller
{
    /**
     * @var Google2FAService
     */
    protected $service;

    /**
     * Google2FAController constructor.
     * @param Google2FAService $google2FAService - Auto injected
     */
    public function __construct(Google2FAService $google2FAService)
    {
        $this->service = $google2FAService;
        $this->middleware('requires.password')->only('disable');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function QRCode(Request $request): JsonResponse
    {
        $user = $request->user();

        $alreadyEnabled = [
            'user' => $user,
            'message' => trans('auth.2fa.already_enabled')
        ];
        $createSecretFailed = [ 'message' => trans('auth.2fa.create_secret_failed') ];
        $QRCodeFailed = [ 'message' => trans('auth.2fa.qrcode_failed') ];

        if ($user->is2FAEnabled()) {
            return response()->json($alreadyEnabled, 400);
        }

        try {
            $user->google2fa_secret = $this->service->generateSecretKey();

            if (! $user->google2fa_secret || ! $user->save()) {
                return response()->json($createSecretFailed, 500);
            }

            $inlineQrCodeUrl = $this->service->getQRCodeInline(
                $user->google2fa_secret,
                $user->email
            );

            $data = [
                '_2FA' => [
                    'QRCode' => (new QRCode())->render($inlineQrCodeUrl),
                    'google2fa_secret' => $user->google2fa_secret
                ]
            ];

            return response()->json($data);
        } catch (\Exception $exception) {
            return response()->json($QRCodeFailed, 500);
        }
    }

    /**
     * @param OTPRequest $request
     * @return JsonResponse
     */
    public function enable(OTPRequest $request): JsonResponse
    {
        $user = $request->user();

        $alreadyEnabled = [
            'user' => $user,
            'message' => trans('auth.2fa.already_enabled')
        ];
        $invalidOTP = [ 'message' => trans('auth.2fa.invalid_otp') ];
        $failed = [ 'message' => trans('auth.2fa.enable_failed') ];

        if ($user->is2FAEnabled()) {
            return response()->json($alreadyEnabled, 400);
        }

        try {
            $OTP = $request->input('one_time_password');
            $isValid = $this->service->checkOTP($user->google2fa_secret, $OTP);

            if (! $isValid) {
                return response()->json($invalidOTP, 422);
            }

            if (! $user->enable2FA()) {
                return response()->json($failed, 500);
            }

            event(new Google2faEnabled($user, new Agent(), $request));

            return response()->json([ 'user' => $user ]);
        } catch (\Exception $exception) {
            return response()->json($failed, 500);
        }
    }

    public function disable(Request $request)
    {
        $failed = ['message' => trans('passwords.2fa_disable_failed')];

        try {
            $user = $request->user();

            if (! $user->disable2FA()) {
                return response()->json($failed);
            }

            $user->refresh();

            $data = [
                'message' => trans('passwords.2fa_disabled'),
                'user' => $user
            ];

            event(new Google2faDisabled($user, new Agent(), $request));

            return response()->json($data);
        } catch (\Exception $e) {
            return response()->json($failed);
        }
    }
}
