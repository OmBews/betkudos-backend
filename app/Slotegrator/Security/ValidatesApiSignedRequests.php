<?php

namespace App\Slotegrator\Security;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Slotegrator\Security\SignApiRequests;

class ValidatesApiSignedRequests
{
    use SignApiRequests;
    
    /**
     * Handle an incoming request from game aggregator.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!$this->check($request)) {
            Log::error('Invalid X-Sing');
            return $this->error();
        }

        return $next($request);
    }

    private function check(Request $request): bool
    {
        $authorizationHeaders = [
            'X-Merchant-Id' => $request->header('X-Merchant-Id'),
            'X-Timestamp' => $request->header('X-Timestamp'),
            'X-Nonce' => $request->header('X-Nonce'),
        ];

        $XSign = $request->header('X-Sign');

        $mergedParams = array_merge($request->all(), $authorizationHeaders);
        
        ksort($mergedParams);

        $hashString = http_build_query($mergedParams);

        $expectedSign = hash_hmac('sha1', $hashString, $this->merchantKey());

        return $XSign === $expectedSign;
    }

    private function error(): array
    {
        return [
            'error_code' => 'INTERNAL_ERROR',
            'error_description' => 'The given X-Sign is invalid',
        ];
    }

    private function merchantKey()
    {
        return config('slotegrator.merchant_key');
    }
}
