<?php

namespace App\Http\Middleware;

use Closure;

class CheckForRestrictedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = $request->user();

        if ($user && $user->isRestricted()) {
            return response()->json(['message' => trans('user.restricted')], 403);
        }

        return $next($request);
    }
}
