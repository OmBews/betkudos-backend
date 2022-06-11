<?php

namespace Tests\Feature\Http\Controllers\API\FeedController;

use App\Models\Leagues\League;
use App\Models\Sports\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SportsUpcomingPreviewTest extends FeedControllerTestCase
{
    public function testUserCanGetPreviewUpcomingMatchesFromPopularLeagues()
    {
        $sport = Sport::query()->first();
        $league = factory(League::class)->create([
            'sport_id' => $sport->getKey(),
            'popular' => true,
            'active' => true
        ]);

        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->upcomingPreviewRoute());

        $response->assertSuccessful();
        $response->assertJsonCount($sport->upcoming_preview_limit, 'data.0.leagues.0.matches');
        $response->assertJsonCount(1, 'data.0.leagues.0.matches.0.markets');
        $response->assertJsonCount(3, 'data.0.leagues.0.matches.0.markets.0.odds');
    }

    public function testUserCanGetPreviewUpcomingMatchesFromNonPopularLeagues()
    {
        $sport = Sport::query()->first();
        $league = factory(League::class)->create([
            'sport_id' => $sport->getKey(),
            'popular' => false,
            'active' => true
        ]);

        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->upcomingPreviewRoute());

        $response->assertSuccessful();
        $response->assertJsonCount($sport->upcoming_preview_limit, 'data.0.leagues.0.matches');
        // Assert that full time result is available
        $response->assertJsonCount(1, 'data.0.leagues.0.matches.0.markets');
        $response->assertJsonCount(3, 'data.0.leagues.0.matches.0.markets.0.odds');
    }

    public function testCanNotGetPreviewUpcomingMatchesIfThereAreNoActivePopularLeagues()
    {
        $sport = Sport::query()->first();
        $league = factory(League::class)->create([
            'sport_id' => $sport->getKey(),
            'popular' => true,
            'active' => false
        ]);

        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->upcomingPreviewRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }

    public function testUserCanGetPreviewUpcomingMatchesFromPopularAndNonPopularLeagues()
    {
        $sport = Sport::query()->first();
        $popularLeague = factory(League::class)->create([
            'sport_id' => $sport->getKey(),
            'popular' => true,
            'active' => true
        ]);
        $nonPopularLeague = factory(League::class)->create([
            'sport_id' => $sport->getKey(),
            'popular' => false,
            'active' => true
        ]);

        $amountOfMatches = $sport->upcoming_preview_limit / 2;

        $this->factoryUpcomingMatches($popularLeague, '+1 hour', $amountOfMatches);
        $this->factoryUpcomingMatches($nonPopularLeague, '+1 hour', $amountOfMatches);

        $response = $this->getJson($this->upcomingPreviewRoute());

        $response->assertSuccessful();

        $response->assertJsonCount($amountOfMatches, 'data.0.leagues.0.matches');
        // Assert that full time result is available
        $response->assertJsonCount(1, 'data.0.leagues.0.matches.0.markets');
        $response->assertJsonCount(3, 'data.0.leagues.0.matches.0.markets.0.odds');

        $response->assertJsonCount($amountOfMatches, 'data.0.leagues.1.matches');
        // Assert that full time result is available
        $response->assertJsonCount(1, 'data.0.leagues.1.matches.0.markets');
        $response->assertJsonCount(3, 'data.0.leagues.1.matches.0.markets.0.odds');
    }

    public function testUpcomingPreviewWillReturnAnEmptyListIfThereAreNoMatchesThatWillHappenInMaxTheNextDay()
    {
        $league = factory(League::class)->create([
            'sport_id' => 1,
            'popular' => true,
            'active' => true
        ]);

        $this->factoryUpcomingMatches($league, '+2 days');

        $response = $this->getJson($this->upcomingPreviewRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }
}
