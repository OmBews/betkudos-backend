<?php

return [
    'token' => env('BETSAPI_TOKEN', null),

    'endpoint' => 'https://api.betsapi.com/',

    'bet365' => [
        'inplay' => 'v1/bet365/inplay',
        'inplay_filter' => 'v1/bet365/inplay_filter',
        'inplay_event' => 'v1/bet365/event',
        'upcoming' => 'v1/bet365/upcoming',
        'result' => 'v1/bet365/result',
        'prematch' => 'v2/bet365/prematch',
    ]
];
