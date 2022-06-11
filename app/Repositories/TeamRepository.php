<?php

namespace App\Repositories;

use App\Contracts\Repositories\TeamRepository as TeamRepositoryInterface;
use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Models\Teams\Team;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TeamRepository extends Repository implements TeamRepositoryInterface
{
    public function __construct(Team $model)
    {
        parent::__construct($model);
    }

    /**
     * @param array $attributes
     * @return Team|null
     * @throws \Exception
     */
    public function create(array $attributes)
    {
        try {
            return parent::create($attributes);
        } catch (\Exception $exception) {
            // Integrity constraint violation (The team already exists)
            if ($exception->getCode() === "23000") {
                return $this->findByBet365Id($attributes['bet365_id']);
            }

            throw $exception;
        }
    }

    public function findByBet365Id(int $bet365Id, bool $fail = false): ?Team
    {
        $team = $this->model
                     ->newQuery()
                     ->where('bet365_id', $bet365Id)
                     ->first();

        if ($fail && is_null($team)) {
            throw new ModelNotFoundException();
        }

        return $team;
    }

    /**
     * @param UpcomingMatch $upcoming
     * @return array
     * @throws \Exception
     */
    public function updateOrCreateFromUpcoming(UpcomingMatch $upcoming): array
    {
        $home = $this->findByBet365Id($upcoming->getHome()->id);
        $away = $this->findByBet365Id($upcoming->getAway()->id);

        if (! is_null($home) && ! is_null($away)) {
            return [$home, $away];
        }

        if (is_null($home)) {
            $attrs = [
                'name' => $upcoming->getHome()->name,
                'bet365_id' => $upcoming->getHome()->id,
            ];

            $home = $this->model->updateOrCreate($attrs);
        }

        if (is_null($away)) {
            $attrs = [
                'name' => $upcoming->getAway()->name,
                'bet365_id' => $upcoming->getAway()->id,
            ];

            $away = $this->model->updateOrCreate($attrs);
        }

        return [$home, $away];
    }
}
