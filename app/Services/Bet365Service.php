<?php

namespace App\Services;

use App\Contracts\BetsAPI\Bet365\Bet365ClientInterface as Bet365Client;

class Bet365Service
{
    /**
     * @var Bet365Client
     */
    private Bet365Client $client;

    public function __construct(Bet365Client $bet365Client)
    {
        $this->client = $bet365Client;
    }

    public function result()
    {
        //
    }
}
