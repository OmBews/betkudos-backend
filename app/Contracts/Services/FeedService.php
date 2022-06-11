<?php

namespace App\Contracts\Services;

use App\Contracts\Repositories\LeagueRepository;
use App\Models\Countries\Country;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use App\Models\Sports\SportCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface FeedService
{
    public const PREVIEW_TIMEFRAME_LIMIT = '+1 day';

    public function match(Event $match): Event;

    public function upcoming(Sport $sport): LengthAwarePaginator;

    public function live(Sport $sport, $featuredLimit = 2): Collection;

    public function preview(): Collection;

    public function livePreview(): Collection;

    public function byCountry(Country $country, Sport $sport): LengthAwarePaginator;

    public function fromToday(Sport $sport, $featuredLimit = 2): LengthAwarePaginator;

    public function startingSoon(Sport $sport): LengthAwarePaginator;

    public function popular(Sport $sport, $isPopular = true): LengthAwarePaginator;

    public function byCategory(SportCategory $category): LengthAwarePaginator;

    public function forgetPreviewCache();
}
