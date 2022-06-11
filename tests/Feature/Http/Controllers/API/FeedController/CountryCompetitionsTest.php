<?php

namespace Tests\Feature\Http\Controllers\API\FeedController;

use App\Models\Countries\Country;
use App\Models\Leagues\League;
use App\Models\Sports\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CountryCompetitionsTest extends FeedControllerTestCase
{
    public function testUserCanGetCountryCompetitionsFromAGivenSport()
    {
        $sport = Sport::query()->first();
        $country = Country::query()->inRandomOrder()->first();
        $leagues = factory(League::class, $leaguesCount = 3)->create([
            'sport_id' => $sport->getKey(),
            'active' => true,
            'cc' => $country->code
        ]);
        $leagues->each(function ($league) {
            $this->factoryUpcomingMatches($league);
        });

        $response = $this->getJson($this->countryCompetitionsRoute($country, $sport));

        $response->assertSuccessful();

        // As factoryUpcomingMatches will create 10 events per league by default
        // The leagues count will be always 3
        $response->assertJsonCount($leaguesCount, 'data');
        $response->assertJsonCount(10, 'data.0.matches');
        $response->assertJsonCount(2, 'data.0.matches.0.markets');
        // Assert that full time result and both teams to score is available
        $response->assertJsonCount(3, 'data.0.matches.0.markets.0.odds');
        $response->assertJsonCount(2, 'data.0.matches.0.markets.1.odds');
    }

    public function testWillGetAEmptyResponseForCountryCompetitionsIfThereAreNoActiveLeagues()
    {
        $sport = Sport::query()->first();
        $country = Country::query()->inRandomOrder()->first();
        $leagues = factory(League::class, $leaguesCount = rand(2, 5))->create([
            'sport_id' => $sport->getKey(),
            'active' => false,
            'cc' => $country->code
        ]);
        $leagues->each(function ($league) {
            $this->factoryUpcomingMatches($league);
        });

        $response = $this->getJson($this->countryCompetitionsRoute($country, $sport));

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }
}
