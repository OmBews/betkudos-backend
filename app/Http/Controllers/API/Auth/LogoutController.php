<?php

namespace App\Http\Controllers\API\Auth;

use App\Events\Auth\Logout;
use App\Http\Controllers\Controller;
use App\Models\Users\User;
use Illuminate\Http\Request;
use Jenssegers\Agent\Agent;

class LogoutController extends Controller
{
    public function logout(Request $request)
    {
        $user = $request->user();

        $this->revokeToken($user);

        event(new Logout($user, new Agent(), $request));

        return response()->json([]);
    }

    protected function revokeToken(User $user)
    {
        return $user->token()->revoke();
    }
}
