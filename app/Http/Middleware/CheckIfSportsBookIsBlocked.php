<?php

namespace App\Http\Middleware;

use App\Models\Users\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class CheckIfSportsBookIsBlocked
{
    /**
     * The URIs that should be reachable while sports book is blocked.
     *
     * @var array
     */
    protected $exceptRoutes = [
        'bost.login',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $blocked = (bool) setting('global.block_sports_book');

        foreach ($this->exceptRoutes as $route) {
            if (Route::currentRouteName() == $route) {
                return $next($request);
            }
        }

        if ($blocked && ! $this->isBookie($request)) {
            return $this->reject();
        }

        return $next($request);
    }

    private function reject()
    {
        $data = [
            "message" => "Service unavailable, try again later."
        ];

        return response()->json($data, 503);
    }

    private function isBookie(Request $request): bool
    {
        if (! Auth::guard('api')->check()) {
            return false;
        }

        $user = $request->user();

        if ($user instanceof User) {
            return $user->hasRole('bookie');
        }

        return false;
    }
}
