<?php

namespace App\Contracts\Repositories;

use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Models\Events\Event;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

interface MatchRepository
{
    /**
     * MatchRepository constructor.
     * @param Event $model
     * @param LeagueRepository $leagueRepository
     */
    public function __construct(Event $model, LeagueRepository $leagueRepository);

    /**
     * @param int $bet365Id
     * @param bool $fail
     * @return Event|null
     * @throws ModelNotFoundException
     */
    public function findByBet365Id(int $bet365Id, bool $fail = false): ?Event;

    /**
     * @param UpcomingMatch $upcoming
     * @return Event
     * @throws \Exception
     */
    public function createFromUpcoming(UpcomingMatch $upcoming): Event;

    public function nextMatches(int $sportId): Collection;

    public function preMatches(int $sportId): Collection;

    public function popular(int $timeStatus, int $sportId = null): Collection;

    /**
     * @param $leagueId
     * @param int $timeStatus
     * @return Collection
     * @throws \InvalidArgumentException
     */
    public function whereLeague($leagueId, int $timeStatus = null): Collection;

    public function upcoming(int $leagueId): Collection;

    public function upcomingBySport(int $sportId, array $relations = []): Collection;
}
