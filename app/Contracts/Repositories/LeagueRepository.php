<?php

namespace App\Contracts\Repositories;

use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Models\Sports\SportCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

interface LeagueRepository
{
    public function __construct(League $model);

    /**
     * @param int $bet365Id
     * @param bool $fail
     * @return League|null
     * @throws ModelNotFoundException
     */
    public function findByBet365Id(int $bet365Id, bool $fail = false): ?League;

    public function findByIdOrName(int $leagueId, string $leagueName): ?League;

    /**
     * @param UpcomingMatch $upcoming
     * @return League
     * @throws \Exception
     */
    public function createFromUpcoming(UpcomingMatch $upcoming): League;

    public function popular(
        int $sportId,
        $popular = true,
        $hasMatches = true,
        $relations = [],
        $timeFrame = Event::UPCOMING_DAYS_LIMIT,
        int $limit = null
    ): Collection;

    public function whereHasUpcomingMatches(int $sportId, $relations = [], string $timeFrame = null);

    public function byCategory(SportCategory $category, $relations = [], string $timeFrame = null);

    public function whereNamesLike(array $names, $relations = [], string $timeFrame = null, bool $hasMatches = true);

    public function fromAContinent($sportId, $relations = [], bool $hasMatches = true): Collection;

    public function fromACountry($countryCode, $sportId, $relations = [], $hasMatches = true): Collection;
}
