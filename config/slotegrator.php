<?php

return [
    'merchant_id' => env('SLOTEGRATOR_MERCHANT_ID'),
    'merchant_key' => env('SLOTEGRATOR_MERCHANT_KEY'),
    'api' => [
        'base_url' => env('SLOTEGRATOR_BASE_URL', 'https://game-aggregator.com/api/v1'),
        'staging_base_url' => env('SLOTEGRATOR_STAGING_BASE_URL', 'https://staging.slotegrator.com/api/index.php/v1/'),
    ]
];
