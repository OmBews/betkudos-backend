<?php

namespace App\Http\Controllers\API;

use App\Contracts\Services\FeedService;
use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Http\Resources\SportResource;
use App\Http\Resources\FeedResource;
use App\Models\Countries\Country;
use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use App\Models\Sports\SportCategory;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\LengthAwarePaginator;

class FeedController extends Controller
{
    /**
     * @var FeedService
     */
    private $service;

    /**
     * FeedController constructor.
     * @param FeedService $feedService
     */
    public function __construct(FeedService $feedService)
    {
        $this->service = $feedService;

        $this->middleware('sets_timezone_by_ip');
    }

    /**
     * @param $status
     * @param Sport $sport
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function popular($status, Sport $sport)
    {
        $leagues = $this->service->popular($sport);

        return FeedResource::collection($leagues)->additional(['sport' => new SportResource($sport)]);
    }

    /**
     * @param Sport $sport
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function fromToday(Sport $sport)
    {
        $paginator = $this->service->fromToday($sport);

        return FeedResource::collection($paginator)->additional(['sport' => new SportResource($sport)]);
    }

    /**
     * @param Sport $sport
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function fromTodayPreview(Sport $sport)
    {
        $paginator = $this->service->fromToday($sport, 1);

        return FeedResource::collection($paginator)->additional(['sport' => new SportResource($sport)]);
    }

    /**
     * @param Sport $sport
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function startingSoon(Sport $sport)
    {
        $paginator = $this->service->startingSoon($sport);

        return FeedResource::collection($paginator)->additional(['sport' => new SportResource($sport)]);
    }

    /**
     * @param Country $country
     * @param Sport $sport
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function byCountry(Country $country, Sport $sport)
    {
        $paginator = $this->service->byCountry($country, $sport);

        return FeedResource::collection($paginator)->additional(['sport' => new SportResource($sport)]);
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function preview()
    {
        return SportResource::collection($this->service->preview());
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function livePreview()
    {
        return SportResource::collection($this->service->livePreview());
    }

    public function upcoming(Sport $sport)
    {
        $leagues = $this->service->upcoming($sport);

        return FeedResource::collection($leagues)->additional(['sport' => new SportResource($sport)]);
    }

    public function live(Sport $sport)
    {
        $leagues = $this->service->live($sport);

        return FeedResource::collection($leagues)->additional(['sport' => new SportResource($sport)]);
    }

    public function liveSportPreview(Sport $sport)
    {
        $leagues = $this->service->live($sport, 1);

        return FeedResource::collection($leagues)->additional(['sport' => new SportResource($sport)]);
    }

    /**
     * @param Sport $sport
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function categories(Sport $sport)
    {
        $categories = $sport->categories()
            ->whereHasLeaguesWithMatches($sport->buildTimeFrameString())
            ->orderBy('name', 'ASC')
            ->get();

        return JsonResource::collection($categories);
    }

    public function byCategory(SportCategory $sportCategory)
    {
        $sport = $sportCategory->sport;
        $leagues = $this->service->byCategory($sportCategory);

        return FeedResource::collection($leagues)->additional(['sport' => new SportResource($sport)]);
    }
}
