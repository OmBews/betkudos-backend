<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetsAppTimezoneByIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $geoIp = geoip($request->ip());

        if (! config('octane.enabled')) {
            config(['app.timezone' => $geoIp->timezone]);
        }

        return $next($request);
    }
}
