<?php

namespace Tests\Feature\Http\Controllers\API\FeedController;

use App\Models\Leagues\League;
use App\Models\Sports\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class PopularLeaguesTest extends FeedControllerTestCase
{
    public function testUserCanGetPopularUpcomingMatches()
    {
        $sport = Sport::all()->first();

        $league = factory(League::class)->create([
            'popular' => true,
            'sport_id' => $sport->getKey(),
        ]);

        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->popularRoute('upcoming', $sport->getKey()));

        $response->assertSuccessful();

        $response->assertJsonCount(10, 'data.0.matches');
        $response->assertJsonCount(2, 'data.0.matches.0.markets');
        $response->assertJsonCount(3, 'data.0.matches.0.markets.0.odds');
        $response->assertJsonCount(2, 'data.0.matches.0.markets.1.odds');
        $this->assertEquals($sport->name, $response->json('sport.name'));
        $this->assertEquals($league->name, $response->json('data.0.name'));
    }

    public function testWillGetAEmptyResponseFromPopularUpcomingMatchesIfThereIsNoActiveLeagues()
    {
        $sport = Sport::all()->first();
        $league = factory(League::class)->create([
            'active' => false,
            'popular' => true,
            'sport_id' => $sport->getKey(),
        ]);
        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->popularRoute('upcoming', $sport->getKey()));

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data' );
    }
}
