<?php

namespace App\Repositories;

use App\Contracts\Repositories\SportsRepository as SportsRepositoryInterface;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use Illuminate\Support\Collection;

class SportsRepository extends Repository implements SportsRepositoryInterface
{
    public function __construct(Sport $model)
    {
        parent::__construct($model);
    }

    public function all(array $relations = []): Collection
    {
        $commonSports = $this->newQuery()->active();
        $sportsHasCategories = $this->newQuery()->active();

        $commonSports->whereHas('matches', function ($query) {
            return $query->upcoming(null, null, '+60 days');
        });

        $sportsHasCategories->whereHas('categories', function ($query) {
            return $query->whereHasLeaguesWithMatches('+60 days');
        });

        $commonSports = $commonSports->orderByDesc('priority')->orderBy('name')->with($relations)->get($this->attributes);
        $sportsHasCategories = $sportsHasCategories->orderBy('name')->with($relations)->get($this->attributes);

        return $commonSports->merge($sportsHasCategories);
    }

    public function soon(array $relations = []): Collection
    {
        $commonSports = $this->newQuery()->active();
        $sportsHasCategories = $this->newQuery()->active();

        $commonSports->whereHas('matches', function ($query) {
            return $query->upcoming(null, null, Event::STARTING_SOON_LIMIT);
        });

        $sportsHasCategories->whereHas('categories', function ($query) {
            return $query->whereHasLeaguesWithMatches(Event::STARTING_SOON_LIMIT);
        });

        $commonSports = $commonSports->orderByDesc('priority')->orderBy('name')->with($relations)->get($this->attributes);
        $sportsHasCategories = $sportsHasCategories->orderBy('name')->with($relations)->get($this->attributes);

        return $commonSports->merge($sportsHasCategories);
    }

    public function find(int $id, bool $fail = false): ?Sport
    {
        if ($fail) {
            return $this->model->findOrFail($id);
        }

        return $this->model->find($id);
    }

    public function live(array $relations = []): Collection
    {
        $query = $this->newQuery();

        $query->whereHas('matches', function ($query) {
            $query->live();
        });

        $sports = $query->onLiveBetting()->orderByDesc('priority')->with($relations)->get();
        
        $sportsIds = $sports->pluck('id')->toArray();
        
        $matches = Event::live($sportsIds, null, ['id', 'sport_id'])->get();
        
        $sports->each(function ($sport) use ($matches) {
            $sport->count = $matches->whereIn('sport_id', $sport->id)->count();
        });

        return $sports;
    }
}
