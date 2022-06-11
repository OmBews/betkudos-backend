<?php

namespace App\Contracts\Http\Clients;

use Amp\Artax\Request;
use Amp\Promise;

interface ClientInterface
{
    public function get(string $uri, array $headers = []): Promise;

    public function newRequest(string $uri, string $method = "GET"): Request;
}
