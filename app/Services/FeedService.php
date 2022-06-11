<?php

namespace App\Services;

use App\Models\Countries\Country;
use App\Models\Markets\Market;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use App\Models\Sports\SportCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use App\Contracts\Services\FeedService as FeedServiceInterface;
use Illuminate\Support\Facades\Cache;

class FeedService implements FeedServiceInterface
{
    private const LEAGUES_PREVIEW_LIMIT = 5;
    private const MATCHES_PER_PAGE = 30;

    public function match(Event $match): Event
    {
        $marketsIds = [];

        if ($match->isLive() || $match->isNotStarted()) {
            $query = Market::query();

            if ($match->isLive()) {
                $query->where('on_live_betting', true);
            }

            $markets = $query->where(function ($query) use ($match) {
                    $query->where('sport_id', $match->sport_id);

                    $query->whereHas('odds', function ($query) use ($match) {
                        $query->where('match_id', $match->getKey())
                              ->where('is_live', $match->isLive());
                    });
                })
                ->get(['id', 'sport_id']);

            $marketsIds = $markets->pluck('id')->toArray();
        }

        $match->load($this->relations($marketsIds, [], $match->isLive()));

        $match->markets = $match->markets->map(function ($matchMarket) {
            $market = $matchMarket->market;
            $market->odds = $matchMarket->odds->sortBy([
                ['name', 'asc'],
                ['order', 'asc'],
            ]);

            return $market;
        })->sortBy([
            ['priority', 'desc'],
            ['popular', 'desc'],
            ['id', 'asc']
        ]);

        return $match;
    }

    public function fromToday(Sport $sport, $featuredLimit = 2): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $this->loadSportRelations($sport, $featuredLimit);

        $marketIds = $sport->featured->pluck('id')->toArray();
        $query = $this->searchUpcoming($marketIds, $sport->bet365_id, Event::TODAY_MATCHES_LIMIT);

        $query->whereHas('league', function ($query) {
            $query->active()->whereNotNull('cc')->select(['bet365_id', 'cc']);
        });

        $query->with($this->relations($marketIds))->orderBy('starts_at');

        return $this->mapIntoLeaguesPaginator($query->paginate(self::MATCHES_PER_PAGE));
    }

    public function startingSoon(Sport $sport): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        $this->loadSportRelations($sport, 1);

        $marketIds = $sport->featured->pluck('id')->toArray();
        $query = $this->searchUpcoming($marketIds, $sport->bet365_id, Event::STARTING_SOON_LIMIT);

        $query->whereHas('league', function ($query) {
            $query->active()->whereNotNull('cc')->select(['bet365_id', 'cc']);
        });

        $query->with($this->relations($marketIds))->orderBy('starts_at');

        return $this->mapIntoLeaguesPaginator($query->paginate(self::MATCHES_PER_PAGE));
    }

    public function upcoming(Sport $sport): LengthAwarePaginator
    {
        $this->loadSportRelations($sport);
        $timeFrame = $sport->buildTimeFrameString();
        $marketIds = $sport->featured->pluck('id')->toArray();

        $query = $this->searchUpcoming($marketIds, $sport->bet365_id, $timeFrame);

        $query->whereHas('league', function ($query) use ($sport) {
            $query->active()->whereNotNull('cc');

            if ($sport->hasLeaguesAsCategories) {
                $query->where(function ($query) use ($sport) {
                    foreach ($sport->categories as $category) {
                        $query->orWhere('name', 'LIKE', "%{$category->name}%");
                    }
                })->orWhereIn('sport_category_id', $sport->categories->pluck('id')->toArray());
            }

            $query->select(['bet365_id', 'cc', 'name', 'sport_category_id']);
        });

        $query->with($this->relations($marketIds))->orderBy('starts_at');

        return $this->mapIntoLeaguesPaginator($query->paginate(self::MATCHES_PER_PAGE));
    }

    public function live(Sport $sport, $featuredLimit = 2): Collection
    {
        $this->loadSportRelations($sport, $featuredLimit, true);
        $marketIds = $sport->featured->pluck('id')->toArray();

        $query = $this->searchLive($marketIds, $sport->bet365_id);

        $query->whereHas('league', function ($query) use ($sport) {
            $query->active()->whereNotNull('cc');

            if ($sport->hasLeaguesAsCategories) {
                $query->where(function ($query) use ($sport) {
                    foreach ($sport->categories as $category) {
                        $query->orWhere('name', 'LIKE', "%{$category->name}%");
                    }
                });
            }

            $query->select(['bet365_id', 'cc', 'name', 'sport_category_id']);
        });

        $relations = $this->relations($marketIds, $this->liveRelations(), true);

        $query->with($relations)->orderBy('starts_at');

        return $this->mapIntoLeagues($query->get());
    }

    public function popular(Sport $sport, $isPopular = true): LengthAwarePaginator
    {
        $this->loadSportRelations($sport);

        $timeFrame = $sport->buildTimeFrameString();
        $marketIds = $sport->featured->pluck('id')->toArray();

        $query = $this->searchUpcoming($marketIds, $sport->bet365_id, $timeFrame);

        $query->whereHas('league', function ($query) {
            $query->active()
                ->popular()
                ->whereNotNull('cc')
                ->select(['bet365_id', 'cc', 'popular']);
        });

        $query->with($this->relations($marketIds))->orderBy('starts_at');

        return $this->mapIntoLeaguesPaginator($query->paginate(self::MATCHES_PER_PAGE));
    }

    public function byCategory(SportCategory $category): LengthAwarePaginator
    {
        $sport = $category->sport;
        $this->loadSportRelations($sport);

        $timeFrame = $sport->buildTimeFrameString();
        $marketIds = $sport->featured->pluck('id')->toArray();

        $query = $this->searchUpcoming($marketIds, $sport->bet365_id, $timeFrame);

        $query->whereHas('league', function ($query) use ($category) {
            $query->active()
                ->where('sport_category_id', $category->getKey())
                ->whereNotNull('cc')
                ->select(['bet365_id', 'cc', 'sport_category_id']);
        });

        $query->with($this->relations($marketIds))->orderBy('starts_at');

        return $this->mapIntoLeaguesPaginator($query->paginate(self::MATCHES_PER_PAGE));
    }

    public function byCountry(Country $country, Sport $sport): LengthAwarePaginator
    {
        $this->loadSportRelations($sport);

        $marketIds = $sport->featured->pluck('id')->toArray();

        $query = $this->searchUpcoming($marketIds, $sport->bet365_id, $sport->buildTimeFrameString());

        $query->whereHas('league', function ($query) use ($country) {
            $query->active()->where('cc', $country->code)->select(['cc', 'bet365_id']);
        });

        $query->with($this->relations($marketIds))->orderBy('starts_at');

        return $this->mapIntoLeaguesPaginator($query->paginate(self::MATCHES_PER_PAGE));
    }

    public function preview(): Collection
    {
        $cacheKey = "sports_upcoming_preview_" . strtolower(config('app.timezone'));
        $sports = Cache::get($cacheKey);

        if (! $sports || (is_countable($sports) && ! count($sports))) {
            $cacheTtl = 3600;
            $sports = $this->sports();

            foreach ($sports as $sport) {
                $marketIds = $sport->featured->pluck('id')->toArray();
                $query = $this->searchUpcoming($marketIds, $sport->bet365_id, self::PREVIEW_TIMEFRAME_LIMIT);

                $query->whereHas('league', function ($query) {
                    $query->active()
                        ->popular()
                        ->whereNotNull('cc')
                        ->orWhere('popular', false)
                        ->whereNotNull('cc')
                        ->select(['bet365_id', 'cc']);
                });

                $matches = $query->with($this->relations($marketIds))
                    ->orderBy('starts_at')
                    ->limit($sport->upcoming_preview_limit)
                    ->get();

                if ($match = $matches->first()) {
                    if ($match->starts_at - time() < $cacheTtl) {
                        $cacheTtl = $match->starts_at - time();
                    }
                }

                $sport->leagues = $this->mapIntoLeagues($matches);
            }

            $sports = $sports->filter(fn($sport) => count($sport->leagues));

            Cache::put($cacheKey, $sports, 60);
        }

        return $sports;
    }

    public function livePreview(): Collection
    {
        $query = Sport::query()->whereHas('matches', function ($matches) {
            $matches->live();
        });

        $sports = $query->get();

        foreach ($sports as $sport) {
            $this->loadSportRelations($sport, 1);
            $marketIds = $sport->featured->pluck('id')->toArray();
            $query = $this->searchLive($marketIds, $sport->bet365_id);

            $query->whereHas('league', function ($query) {
                $query->active()
                    ->popular()
                    ->whereNotNull('cc')
                    ->orWhere('popular', false)
                    ->whereNotNull('cc')
                    ->orderBy('popular')
                    ->select(['bet365_id', 'cc']);
            });

            $matches = $query->with($this->relations($marketIds, $this->liveRelations(), true))
                ->orderBy('starts_at')
                ->limit($sport->live_preview_limit)
                ->get();

            $sport->leagues = $this->mapIntoLeagues($matches);
        }

        return $sports->filter(fn($sport) => count($sport->leagues));
    }

    public function forgetPreviewCache()
    {
        $timezones = timezone_identifiers_list();

        foreach ($timezones as $key => $tz) {
            Cache::forget("sports_upcoming_preview_" . strtolower($tz));
        }
    }

    private function sports(): \Illuminate\Database\Eloquent\Collection
    {
        if (! $sports = Cache::get('all_sports')) {
            $sports = Sport::query()->whereHas('matches', function ($matches) {
                $matches->upcoming(null, null, self::PREVIEW_TIMEFRAME_LIMIT);
            })->get();
            $sports->each(fn($sport) => $this->loadSportRelations($sport, 1));

            Cache::put('all_sports', $sports, 3600);
        }

        return $sports;
    }

    private function loadSportRelations(Sport $sport, $featuredLimit = 2, bool $live = false)
    {
        $sport->load($this->sportRelations($featuredLimit, [], $live));
    }

    private function sportRelations($featuredLimit = 2, array $overrides = [], bool $live = false): array
    {
        $relations = [
            'categories:id,sport_id,name',
            'featured' => function ($query) use ($featuredLimit, $live) {
                if ($live) {
                    $query->where('on_live_betting', true);
                }

                $query->select([
                    'id', 'name', 'sport_id',
                    'key', 'featured', 'priority',
                    'headers', 'featured_header'
                ]);

                if ($featuredLimit) {
                    $query->take($featuredLimit);
                }
            }
        ];

        return array_merge($relations, $overrides);
    }

    /**
     * @param array $markets
     * @param int|null $sportId
     * @param string $timeFrame
     * @return Event|\Illuminate\Database\Eloquent\Builder
     */
    protected function searchUpcoming(array $markets = [], int $sportId = null, $timeFrame = Event::UPCOMING_DAYS_LIMIT)
    {
        $fields = [
            'id', 'league_id', 'sport_id', 'starts_at',
            'time_status', 'home_team_id', 'away_team_id'
        ];

        return Event::upcoming($sportId, null, $timeFrame, $fields, $this->timezone());
    }

    /**
     * @param array $markets
     * @param int|array|null $sportId
     * @return Event|\Illuminate\Database\Eloquent\Builder
     */
    protected function searchLive(array $markets = [], $sportId = null)
    {
        $fields = [
            'id', 'league_id', 'sport_id', 'starts_at',
            'time_status', 'home_team_id', 'away_team_id'
        ];

        return Event::live($sportId, null, $fields, $this->timezone())->whereHasOdds($markets, true);
    }

    private function relations(array $marketIds = [], array $overrides = [], $isLive = false)
    {
        $relations = [
            'league:id,name,cc,active,sport_id,bet365_id',
            'league.country:id,name,code',
            'markets' => function ($query) use ($marketIds) {
                $query->whereIn('market_id', $marketIds)
                    ->with('market')
                    ->orderBy('order')
                    ->select([
                        'id', 'match_id', 'market_id'
                    ]);
            },
            'home:name,bet365_id,image_id,id',
            'away:name,bet365_id,image_id,id',
            'markets.odds' => function ($query) use ($isLive) {
                $query->where('is_live', $isLive)->where('odds', '>', 0)->orderByRaw("`order`= NULL, `order`")->select([
                    'id','market_id','match_id',
                    'match_market_id','odds','name',
                    'header','handicap', 'is_suspended',
                    'is_live', 'order'
                ]);
            },
        ];

        return array_merge($relations, $overrides);
    }

    /**
     * @param LengthAwarePaginator $paginator
     * @return LengthAwarePaginator
     */
    private function mapIntoLeaguesPaginator(LengthAwarePaginator $paginator): LengthAwarePaginator
    {
        $collection = collect($paginator->items());

        $leagues = $this->mapIntoLeagues($collection);

        return new LengthAwarePaginator(
            $leagues,
            $paginator->total(),
            $paginator->perPage(),
            $paginator->currentPage()
        );
    }

    private function mapIntoLeagues(Collection $collection): Collection
    {
        $leagues = $collection->map(fn($match) => $match->league)->unique();
        $leagues->each(function ($league) use ($collection) {
            $matches = $collection->where('league_id', '=', $league->bet365_id);

            $matches = $matches->sortBy('id');

            $league->matches = $matches;

            return $league;
        });

        return $leagues->sortBy([
            ['name', 'asc']
        ]);
    }

    private function timezone(): string
    {
        $geoIp = geoip(request()->ip());

        return $geoIp->timezone;
    }

    private function liveRelations(): array
    {
        return [
            'result:id,match_id,current_time,single_score,is_playing,points,kick_of_time,passed_minutes,passed_seconds,quarter',
            'stats:id,match_id,events,stats'
        ];
    }
}
