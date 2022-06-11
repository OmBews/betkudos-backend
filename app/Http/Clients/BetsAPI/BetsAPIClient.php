<?php

namespace App\Http\Clients\BetsAPI;

use App\Http\Clients\Client;

abstract class BetsAPIClient extends Client
{
    protected $token;

    protected $baseUrl;

    public function __construct(string $token = null)
    {
        parent::__construct();

        $this->baseUrl = config('betsapi.endpoint');
        $this->token = $token ? $token : config('betsapi.token');
    }

    protected function buildUriQuery(string $uri, array $params = []): string
    {
        $uri .= "?";

        $params['token'] = $this->token;

        $clearedParams = array_filter($params, function ($value) {
            return $value;
        });

        return $uri . http_build_query($clearedParams);
    }
}
