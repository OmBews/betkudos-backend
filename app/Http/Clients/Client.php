<?php

namespace App\Http\Clients;

use Amp\Artax\DefaultClient;
use Amp\Artax\Request;
use Amp\Promise;
use App\Contracts\Http\Clients\ClientInterface;

class Client implements ClientInterface
{
    /**
     * @var DefaultClient
     */
    protected $http;

    public function __construct()
    {
        $this->http = new DefaultClient();
    }

    public function get(string $uri, array $headers = []): Promise
    {
        $request = $this->newRequest($uri);

        $request->withHeaders($headers);

        return $this->http->request($request);
    }

    public function newRequest(string $uri, string $method = "GET"): Request
    {
        return new Request($uri, $method);
    }
}
