<?php

namespace App\Http\Controllers\Bost\Auth;

use App\Models\Users\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\API\Auth\LoginController as BaseLoginController;

class LoginController extends BaseLoginController
{
    /**
     * @var bool
     */
    protected $requires2FA = false;

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        return parent::login($request);
    }

    /**
     * @param Request $request
     * @param User $user
     * @param $token
     * @return JsonResponse
     */
    protected function authenticated(Request $request, User $user, $token): JsonResponse
    {
        $unauthorized = ['message' => trans('auth.forbidden')];

        if (! $user->hasAnyRole('bookie')) {
            return response()->json($unauthorized, 403);
        }

        return parent::authenticated($request, $user, $token);
    }
}
