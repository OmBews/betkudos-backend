<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Countries\Country;
use App\Models\Leagues\League;
use App\Models\Sports\Sport;
use Illuminate\Http\Resources\Json\JsonResource;

class CountryController extends Controller
{
    public function __construct()
    {
        $this->middleware('sets_timezone_by_ip');
    }

    /**
     * @param Sport $sport
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function withMatchesBySport(Sport $sport)
    {
        $countries = Country::whereHasMatches($sport->getKey())->get();

        $worldLeagues = Country::whereIsInternational()
            ->whereHasLeaguesWithMatches($sport->getKey())
            ->get();

        $continentalLeagues = Country::whereIsInternationalClubs()
            ->whereHasLeaguesWithMatches($sport->getKey())
            ->get();

        if (! count($continentalLeagues)) {
            if (League::whereHasMatchesFromAContinent($sport->getKey())->get()->count()) {
                $continentalLeagues->push(Country::internationalClubs());
            }
        }

        $countriesCollection = collect($countries->all())
            ->merge($worldLeagues->all())
            ->merge($continentalLeagues->all())
            ->sortBy(function ($country) {
                return $country['name'];
            });

        return JsonResource::collection($countriesCollection);
    }
}
