<?php

namespace Tests\Feature\Http\Controllers\API;

use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Http\Controllers\API\FeedController\FeedControllerTestCase;

class MatchControllerTest extends FeedControllerTestCase
{
    use RefreshDatabase;

    protected function showRoute(Event $match)
    {
        return route('matches.show', ['match' => $match->getKey()]);
    }

    public function betableStatusesProvider(): array
    {
        return [
          [Event::STATUS_IN_PLAY],
          [Event::STATUS_NOT_STARTED]
        ];
    }

    public function invalidStatusesProvider(): array
    {
        return [
          [Event::STATUS_ABANDONED],
          [Event::STATUS_CANCELLED],
          [Event::STATUS_ENDED],
          [Event::STATUS_INTERRUPTED],
          [Event::STATUS_POSTPONED],
          [Event::STATUS_WALKOVER],
          [Event::STATUS_TO_BE_FIXED],
          [Event::STATUS_RETIRED],
        ];
    }

    /**
     * @param $status
     * @dataProvider betableStatusesProvider
     */
    public function testUserCanGetDataFromAMatch($status)
    {
        $league = factory(League::class)->create([
           'sport_id' => Sport::SOCCER_SPORT_ID
        ]);

        $match = $this->factoryUpcomingMatches($league, $status ? '-5 minutes' : '+5 minutes', 1, $status)->first();

        $response = $this->getJson($this->showRoute($match));

        $response->assertSuccessful();

        $this->assertEquals($match->id, $response->json('data.id'));
        $this->assertEquals($match->isLive(), $response->json('data.is_live'));

        $response->assertJsonCount(2, 'data.markets');
    }

    /**
     * @param $status
     * @dataProvider invalidStatusesProvider
     */
    public function testWillNotLoadMarketsDataWhenTheEventHaveNotABetableStatus($status)
    {
        $league = factory(League::class)->create([
            'sport_id' => Sport::SOCCER_SPORT_ID
        ]);

        $match = $this->factoryUpcomingMatches($league, null, 1, $status)->first();

        $response = $this->getJson($this->showRoute($match));

        $response->assertSuccessful();

        $this->assertEquals($match->id, $response->json('data.id'));
        $this->assertEquals(false, $response->json('data.is_live'));
        // Assert that there's no markers available
        $response->assertJsonCount(0, 'data.markets');
    }
}
