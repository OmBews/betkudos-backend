<?php

namespace App\Http\Middleware;

use App\Contracts\Repositories\NotAllowedIpRepository;
use App\Models\NotAllowedIps\NotAllowedIp;
use Closure;

class NotAllowedIpAddress
{
    private $notAllowedIps;

    public function __construct(NotAllowedIpRepository $notAllowedIps)
    {
        $this->notAllowedIps = $notAllowedIps;
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->notAllowedIps->find($request->ip()) instanceof NotAllowedIp) {
            $message = trans('user.ip_blocked');

            if ($request->isXmlHttpRequest()) {
                return response()->json(['message' => $message], 403);
            }

            abort(403, $message);
        }

        return $next($request);
    }
}
