<?php

namespace App\Slotegrator;

use \Illuminate\Http\Client\Response;

interface SlotegratorInterface
{
    public function games(): Response;

    public function gameLobby(string $gameUuid, string $currency, string $technology = null): Response;

    public function gameInit(
        string $gameUuid,
        string|int $playerId,
        string $playerName,
        string $currency,
        string $sessionId,
        array $options = []
    ): Response;

    public function gameInitDemo(string $gameUuid, array $options = []): Response;

    public function balanceNotify(int $sessionId, int|float $balance);

    public function baseUrl(): string;

    public function freeSpinBet(string $gameId, string $currency);
    
    public function freeSpinSet(string $playerId, string $playerName, string $currency, int $qty, int $vfrom, int $vuntil, string $spinId, string $gameId, int $bet_id = null): Response;

    public function freeSpinGet(string $freespinId);

    public function validate();
}
