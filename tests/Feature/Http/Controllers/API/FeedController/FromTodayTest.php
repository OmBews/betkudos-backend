<?php

namespace Tests\Feature\Http\Controllers\API\FeedController;

use App\Models\Leagues\League;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FromTodayTest extends FeedControllerTestCase
{
    public function testUserCanGetMatchesFromTodayBySport()
    {
        $league = factory(League::class)->create([
            'sport_id' => $sportId = 1,
        ]);

        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->fromTodayRoute($sportId));

        $response->assertSuccessful();

        $response->assertJsonCount(10, 'data.0.matches');
        $response->assertJsonCount(2, 'data.0.matches.0.markets');
        $response->assertJsonCount(3, 'data.0.matches.0.markets.0.odds');
        $response->assertJsonCount(2, 'data.0.matches.0.markets.1.odds');

        $this->assertEquals($league->name, $response->json('data.0.name'));
    }

    public function testWillGetAEmptyResponseFromTodayMatchesBySportIfThereIsNoActiveLeagues()
    {
        $league = factory(League::class)->create([
            'sport_id' => $sportId = 1,
            'active' => false
        ]);

        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->fromTodayRoute($sportId));

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }

    public function testFromTodayEndpointShouldReturnNotFoundIfTheSportDoesNotExists()
    {
        $response = $this->getJson($this->fromTodayRoute(rand(100, 199)));

        $response->assertNotFound();
    }
}
