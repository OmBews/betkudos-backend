<?php

namespace App\Slotegrator;

use App\Slotegrator\Security\SignApiRequests;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Slotegrator implements SlotegratorInterface
{
    use SignApiRequests;

    private PendingRequest $http;

    public function __construct()
    {
        $this->http = Http::baseUrl($this->baseUrl());
    }

    public function games(int $page = 1): Response
    {
        $params = ['page' => $page];

        return $this->http->withHeaders($this->buildHeaders($params))->get('/games', $params);
    }

    /*
    |--------------------------------------------------------------------------
    | Free spin bet campaign
    |--------------------------------------------------------------------------
    */
    public function freeSpinBet(string $gameId, string $currency):Response
    {
        $params = [
            'game_uuid' => $gameId,
            'currency' => $currency
        ];

        return $this->http->withHeaders($this->buildHeaders($params))->get('/freespins/bets', $params);
    }

    /*
    |--------------------------------------------------------------------------
    | Free spin set
    |--------------------------------------------------------------------------
    */
    public function freeSpinSet(string $playerId, string $playerName, string $currency, int $qty, int $vfrom, int $vuntil, string $spinId, string $gameId, int $bet_id = null):Response
    {
        $params = [
            'player_id' => $playerId,
            'player_name' => $playerName,
            'currency' => $currency,
            'quantity' => $qty,
            'valid_from' => $vfrom,
            'valid_until' => $vuntil,
            'freespin_id' => $spinId,
            'game_uuid' => $gameId,
            'total_bet_id' => $bet_id 
        ];
        
        return $this->http->withHeaders($this->buildHeaders($params))->acceptJson()->asForm()->post('/freespins/set', $params);
    }

    /*
    |--------------------------------------------------------------------------
    | Free spin get 
    |--------------------------------------------------------------------------
    */
    public function freeSpinGet(string $freespinId):Response
    {
        $params = [
            'freespin_id' => $freespinId
        ];
        
        return $this->http->withHeaders($this->buildHeaders($params))->get('/freespins/get', $params);
    }

    public function gameLobby(string $gameUuid, string $currency, string $technology = null): Response
    {
        $params = [
            'game_uuid' => $gameUuid,
            'currency' => $currency,
        ];

        if ($technology) {
            $params = array_merge($params, ['technology' => $technology]);
        }

        return $this->http->withHeaders($this->buildHeaders($params))->get('/games/lobby', $params);
    }

    public function gameInit(string $gameUuid, int|string $playerId, string $playerName, string $currency, string $sessionId, array $options = []): Response
    {
        $params = [
            'game_uuid' => $gameUuid,
            'player_id' => $playerId,
            'currency' => $currency,
            'player_name' => $playerName,
            'session_id' => $sessionId
        ];

        $params = array_merge($params, $options);

        return $this->http->withHeaders($this->buildHeaders($params))->acceptJson()->asForm()->post('/games/init', $params);
    }

    public function gameInitDemo(string $gameUuid, array $options = []): Response
    {
        $params = [
            'game_uuid' => $gameUuid,
        ];

        $params = array_merge($params, $options);
        return $this->http->withHeaders($this->buildHeaders($params))->acceptJson()->asForm()->post("/games/init-demo", $params);
    }

    public function validate(array $options = []) 
    {
        $params = [];
        $params = array_merge($params, $options);
        return $this->http->withHeaders($this->buildHeaders($params))->acceptJson()->asForm()->post("/self-validate", $params);
    }

    public function balanceNotify(int $sessionId, int|float $balance)
    {
        Log::error("Balance Notify hiting....");
        $params = [
            'balance' => $balance,
            'session_id' => $sessionId
        ];

        return $this->http->withHeaders($this->buildHeaders($params))->acceptJson()->asForm()->post("/balance/notify", $params);
    }

    public function baseUrl(): string
    {
        if (app()->environment('production')) {
            config('slotegrator.api.staging_base_url');
        }

        return config('slotegrator.api.staging_base_url');
    }

    private function buildHeaders(array $requestParams = []): array
    {
        $headers = [
            'X-Merchant-Id' => $this->merchantId(),
            'X-Timestamp' => time(),
            'X-Nonce' => $this->nonce(),
        ];

        return array_merge($headers, [
            'X-Sign' => $this->sing($headers, $requestParams),
        ]);
    }
}
