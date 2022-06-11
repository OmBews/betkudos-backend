<?php

namespace App\Slotegrator\Security;

trait SignApiRequests
{
    public function sing(array $headers, array $requestParams = []): bool|string
    {
        $mergedParams = array_merge($requestParams, $headers);

        ksort($mergedParams);

        $hashString = http_build_query($mergedParams);

        return hash_hmac('sha1', $hashString, $this->merchantKey());
    }

    protected function merchantKey()
    {
        return config('slotegrator.merchant_key');
    }

    protected function merchantId()
    {
        return config('slotegrator.merchant_id');
    }

    protected function nonce(): string
    {
        return md5(uniqid(mt_rand(), true));
    }
}
