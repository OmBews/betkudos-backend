<?php

namespace App\Contracts\Repositories;

use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Models\Teams\Team;

interface TeamRepository
{
    public function findByBet365Id(int $bet365Id, bool $fail = false): ?Team;

    public function updateOrCreateFromUpcoming(UpcomingMatch $upcoming): array;
}
