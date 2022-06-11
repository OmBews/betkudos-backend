<?php

namespace App\Repositories;

use App\Contracts\Repositories\MatchRepository as MatchRepositoryInterface;
use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Contracts\Repositories\LeagueRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class MatchRepository extends Repository implements MatchRepositoryInterface
{
    protected $leagueRepository;

    public function __construct(Event $model, LeagueRepository $leagueRepository)
    {
        parent::__construct($model);

        $this->leagueRepository = $leagueRepository;
    }

    /**
     * @inheritDoc
     */
    public function findByBet365Id(int $bet365Id, bool $fail = false): ?Event
    {
        $match = $this->model
                      ->newQuery()
                      ->where('bet365_id', $bet365Id)
                      ->first();

        if ($fail && is_null($match)) {
            throw new ModelNotFoundException();
        }

        return $match;
    }

    /**
     * @inheritDoc
     */
    public function createFromUpcoming(UpcomingMatch $upcoming): Event
    {
        $matchId = $upcoming->getId();
        $match = $this->findByBet365Id($matchId);

        if ($match instanceof Event && ! is_null($match)) {
            return $match;
        }

        try {
            $attrs = [
                'bet365_id' => $upcoming->getId(),
                'bets_api_id' => $upcoming->getBetsAPIId(),
                'sport_id' => $upcoming->getSportId(),
                'home_team_id'  => $upcoming->getHome()->id,
                'away_team_id'  => $upcoming->getAway()->id,
                'league_id'  => $upcoming->getLeague()->id,
                'starts_at'  => $upcoming->getTime(),
                'time_status'  => $upcoming->getTimeStatus(),
                'last_bets_api_update'  => $upcoming->getUpdatedAt(),
            ];

            return $this->create($attrs);
        } catch (\Exception $exception) {
            // Integrity constraint violation (The match already exists)
            if ($exception->getCode() === "23000") {
                return $this->findByBet365Id($matchId);
            }

            throw $exception;
        }
    }

    public function nextMatches(int $sportId): Collection
    {
        return $this->newQuery()
                    ->where('sport_id', $sportId)
                    ->where('starts_at', '>=', strtotime('-6 hours'))
                    ->where('starts_at', '<=', strtotime('+6 hours'))
                    ->where('time_status', Event::STATUS_NOT_STARTED)
                    ->with(['league', 'home', 'away'])
                    ->get();
    }

    public function preMatches(int $sportId): Collection
    {
        return $this->newQuery()
                    ->where('sport_id', $sportId)
                    ->where('starts_at', '>=', strtotime('+6 hours'))
                    ->where('time_status', Event::STATUS_NOT_STARTED)
                    ->with(['league', 'home', 'away'])
                    ->get();
    }

    public function popular(int $timeStatus, int $sportId = null): Collection
    {
        $leagues = $this->leagueRepository->popular($sportId);

        $query = $this->newQuery();

        if ($sportId) {
            $query = $query->where('sport_id', $sportId);
        }

        return $query->whereIn('league_id', $leagues->pluck('bet365_id'))
                     ->where('time_status', $timeStatus)
                     ->get();
    }

    /**
     * @inheritDoc
     */
    public function whereLeague($leagueId, int $timeStatus = null): Collection
    {
        $query = $this->newQuery();

        if (! is_null($timeStatus)) {
            $query = $query->where('time_status', $timeStatus);
        }

        if (is_array($leagueId)) {
            return $query->whereIn('league_id', $leagueId)->get();
        } elseif (is_numeric($leagueId)) {
            return $query->where('league_id', $leagueId)->get();
        }

        throw new \InvalidArgumentException("The leagueId should be integer or array");
    }

    public function upcoming(int $leagueId): Collection
    {
        $query = $this->newQuery();

        $query = $query->where('starts_at', '>', time())
                       ->where('starts_at', '<', strtotime('+3 days'));

        return $query->where('league_id', $leagueId)->get();
    }

    public function upcomingBySport(int $sportId, array $relations = []): Collection
    {
        $query = $this->newQuery();

        $query = $query->where('starts_at', '>', time())
                       ->where('time_status', Event::STATUS_NOT_STARTED);

        return $query->where('sport_id', $sportId)->with($relations)->get();
    }
}
