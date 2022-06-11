<?php

namespace Tests\Feature\Http\Controllers\API\FeedController;

use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SportsLivePreviewTest extends FeedControllerTestCase
{
    use RefreshDatabase;

    protected function route()
    {
        return route('feed.live-preview');
    }

    public function testUserCanGetPreviewLiveEvents()
    {
        $sport = Sport::query()->first();
        $league = factory(League::class)->create([
            'sport_id' => $sport->getKey()
        ]);

        $this->factoryUpcomingMatches($league, '-5 minutes', 10, Event::STATUS_IN_PLAY);

        $response = $this->getJson($this->route());

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonCount(1, 'data.0.leagues');
        $response->assertJsonCount($sport->live_preview_limit, 'data.0.leagues.0.matches');
    }

    public function testUserCanNotGetPreviewLiveEventsIfThereIsNoLiveEvents()
    {
        $response = $this->getJson($this->route());

        $response->assertSuccessful();
        $response->assertJsonCount(0, 'data');
    }
}
