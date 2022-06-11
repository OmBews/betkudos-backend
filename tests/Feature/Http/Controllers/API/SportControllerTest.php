<?php

namespace Tests\Feature\Http\Controllers\API;

use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Http\Controllers\API\FeedController\FeedControllerTestCase;
use Tests\TestCase;

class SportControllerTest extends FeedControllerTestCase
{
    use RefreshDatabase;

    protected function indexRoute(): string
    {
        return route('sports.index');
    }

    protected function liveRoute(): string
    {
        return route('sports.live');
    }

    public function testUserCanListAllSportsWithUpcomingEvents()
    {
        $sport = Sport::query()->first();
        $league = factory(League::class)->create([
            'sport_id' => $sport->getKey(),
            'active' => true
        ]);

        // @Todo Allow factoryUpcomingMatches() support more than one sport
        $this->factoryUpcomingMatches($league);

        $response = $this->getJson($this->indexRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data');
    }

    public function testWillNotListSportsIfThereIsNoUpcomingEvents()
    {
        $response = $this->getJson($this->indexRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }

    public function testUserCanListAllSportsWithEvents()
    {
        $sport = Sport::query()->first();
        $league = factory(League::class)->create([
            'sport_id' => $sport->getKey(),
            'active' => true
        ]);

        // @Todo Allow factoryUpcomingMatches() support more than one sport
        $this->factoryUpcomingMatches($league, '-5 minutes', 10, Event::STATUS_IN_PLAY);

        $response = $this->getJson($this->liveRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data');
    }

    public function testWillNotListLiveSportsIfThereIsNoLiveEvents()
    {
        $response = $this->getJson($this->liveRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }
}
