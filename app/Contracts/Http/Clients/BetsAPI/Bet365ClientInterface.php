<?php

namespace App\Contracts\Http\Clients\BetsAPI;

use Amp\Promise;

interface Bet365ClientInterface
{
    public function inPlay(bool $raw = false): Promise;

    public function inPlayFilter(int $sportId = null, int $leagueId = null): Promise;

    public function inPlayEvent(int $fixtureId, bool $stats = false, bool $lineUp = false, bool $raw = false): Promise;

    public function upcoming(int $sportId, ?string $day = null, ?int $page = null, ?int $leagueId = null): Promise;

    public function preMatch(int $fixtureId, bool $raw = false);

    public function result(int $eventId): Promise;

    /**
     * @param int $days
     * @return string
     */
    public static function formatSearchDate(int $days = 0): string;
}
