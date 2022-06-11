<?php

namespace App\Repositories;

use App\Contracts\Repositories\LeagueRepository as LeagueRepositoryInterface;
use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Models\Sports\SportCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class LeagueRepository extends Repository implements LeagueRepositoryInterface
{
    public function __construct(League $model)
    {
        parent::__construct($model);
    }

    /**
     * @param array $attributes
     * @return League|null
     * @throws \Exception
     */
    public function create(array $attributes)
    {
        try {
            return parent::create($attributes);
        } catch (\Exception $exception) {
            // Integrity constraint violation (The league already exists)
            if ($exception->getCode() === "23000") {
                if (isset($attributes['bet365_id']) && isset($attributes['name'])) {
                    return $this->findByIdOrName($attributes['bet365_id'], $attributes['name']);
                }
            }

            throw $exception;
        }
    }


    /**
     * @inheritDoc
     */
    public function createFromUpcoming(UpcomingMatch $upcoming): League
    {
        $leagueId = $upcoming->getLeague()->id;
        $leagueName = $upcoming->getLeague()->name;

        $league = $this->findByIdOrName($leagueId, $leagueName);

        if (! is_null($league) && $league instanceof League) {
            return $league;
        }

        $attrs = [
            'bet365_id' => $leagueId,
            'name' => $upcoming->getLeague()->name,
            'sport_id' => $upcoming->getSportId()
        ];

        return $this->create($attrs);
    }

    /**
     * @inheritDoc
     */
    public function findByBet365Id(int $bet365Id, bool $fail = false): ?League
    {
        $league = $this->newQuery()
                       ->where('bet365_id', $bet365Id)
                       ->first($this->attributes);

        if ($fail && is_null($league)) {
            throw new ModelNotFoundException();
        }

        return $league;
    }

    public function popular(
        int $sportId = null,
        $popular = true,
        $hasMatches = true,
        $relations = [],
        $timeFrame = Event::UPCOMING_DAYS_LIMIT,
        int $limit = null
    ): Collection {
        $query = $this->newQuery()
                      ->whereNotNull('cc')
                      ->active()
                      ->popular($popular);

        if ($sportId) {
            $query = $query->where('sport_id', $sportId);
        }

        if ($hasMatches) {
            $query = $query->whereHasUpcomingMatches(null, null, $timeFrame);
        }

        if ($limit) {
            $query = $query->limit($limit);
        }

        return $query->with($relations)->get($this->attributes);
    }

    public function whereHasUpcomingMatches(int $sportId, $relations = [], string $timeFrame = null)
    {
        return $this->newQuery()
                    ->whereNotNull('cc')
                    ->sport($sportId)
                    ->active()
                    ->whereHasUpcomingMatches(null, null, $timeFrame)
                    ->orderBy(
                        Event::select('starts_at')
                            ->upcoming(null, null, $timeFrame)
                            ->whereColumn('league_id', 'leagues.bet365_id')
                            ->orderBy('starts_at')
                            ->limit(1)
                    )
                    ->with($relations)
                    ->get($this->attributes);
    }

    public function byCategory(SportCategory $category, $relations = [], string $timeFrame = null)
    {
        return $this->newQuery()
                    ->where('sport_category_id', $category->getKey())
                    ->whereNotNull('cc')
                    ->active()
                    ->whereHasUpcomingMatches(null, null, $timeFrame)
                    ->orderBy(
                        Event::select('starts_at')
                            ->upcoming(null, null, $timeFrame)
                            ->whereColumn('league_id', 'leagues.bet365_id')
                            ->orderBy('starts_at')
                            ->limit(1)
                    )
                    ->with($relations)
                    ->get($this->attributes);
    }

    public function whereNamesLike(array $names, $relations = [], string $timeFrame = null, bool $hasMatches = true)
    {
        $query = $this->newQuery();

        foreach ($names as $name) {
            $query->orWhere('name', 'LIKE', "%$name%")->whereNotNull('cc')->active();

            if ($hasMatches) {
                $query->whereHasUpcomingMatches(null, null, $timeFrame);
            }
        }

        return $query->with($relations)->get($this->attributes);
    }

    public function findByIdOrName(int $leagueId, string $leagueName): ?League
    {
        return $this->newQuery()
            ->where('name', $leagueName)
            ->orWhere('bet365_id', $leagueId)
            ->first($this->attributes);
    }

    public function fromAContinent($sportId, $relations = [], bool $hasMatches = true): Collection
    {
        return $this->newQuery()
                    ->fromAContinent($sportId, $hasMatches)
                    ->with($relations)
                    ->get($this->attributes);
    }
    public function fromACountry($countryCode, $sportId, $relations = [], $hasMatches = true): Collection
    {
        return $this->newQuery()
                    ->fromACountry($countryCode, $sportId, $hasMatches)
                    ->with($relations)
                    ->get($this->attributes);
    }
}
