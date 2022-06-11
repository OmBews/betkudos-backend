<?php

namespace Tests\Feature\Http\Controllers\API;

use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Event;
use Database\Seeders\MarketGroupsTableSeeder;
use Database\Seeders\SettingsTableSeeder;
use Database\Seeders\SoccerMarketsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class BetSlipControllerTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingsTableSeeder::class);
        $this->seed(MarketGroupsTableSeeder::class);
        $this->seed(SoccerMarketsSeeder::class);
    }

    protected function updateRoute(): string
    {
        return route('api.bet-slip.update');
    }

    public function testCanUpdateTheBetSlipSelections()
    {
        $match = factory(Event::class)->create([
            'time_status' => Event::STATUS_NOT_STARTED
        ]);

        $market = (new Market())->fullTimeResult();
        $market->odds()->save(factory(MarketOdd::class)->make([
            'match_id' => $match->getKey(),
            'odds' => $oldOdds = 2.10,
            'is_live' => false
        ]));
        $selection = $market->odds()->first();
        $selection->update(['odds' => $newOdds = 2.00]);

        $payload = [
            'selections' => [
                [
                    'id' => $selection->id,
                    'match_id' => $match->getKey(),
                    'odds' => $oldOdds
                ],
            ]
        ];

        $response = $this->postJson($this->updateRoute(), $payload);

        $response->assertSuccessful();
        // Assert that the odds have changed and the market is not suspended
        $selection->fresh();
        $this->assertEquals($newOdds, $selection->odds);
        $this->assertEquals($response->json('data.0.odds'), $selection->odds);
        $this->assertTrue($response->json('data.0.changed'));
        $this->assertFalse($response->json('data.0.suspended'));
        $this->assertTrue($response->json('data.0.available'));
    }

    public function testWillSetASelectionAsSuspendedWhenTheMarketCanNotGoLive()
    {
        $match = factory(Event::class)->create([
            'time_status' => Event::STATUS_IN_PLAY,
            'starts_at' => strtotime('-5 minutes'),
        ]);

        $market = (new Market())->fullTimeResult();
        $market->odds()->save(factory(MarketOdd::class)->make([
            'match_id' => $match->getKey(),
            'is_live' => true
        ]));
        $market->on_live_betting = false;
        $market->save();
        $selection = $market->odds()->first();
        $selection->update();

        $payload = [
            'selections' => [
                [
                    'id' => $selection->id,
                    'match_id' => $match->getKey(),
                    'odds' => $selection->odds
                ],
            ]
        ];

        $response = $this->postJson($this->updateRoute(), $payload);

        $response->assertSuccessful();

        $selection->fresh();

        $this->assertEquals($response->json('data.0.odds'), $selection->odds);
        $this->assertFalse($response->json('data.0.changed'));
        $this->assertTrue($response->json('data.0.suspended'));
        $this->assertTrue($response->json('data.0.available'));
    }

    public function testWillSetASelectionAsSuspendedWhenTheSelectionIsCurrentlySuspended()
    {
        $match = factory(Event::class)->create([
            'time_status' => Event::STATUS_IN_PLAY,
            'starts_at' => strtotime('-5 minutes'),
        ]);

        $market = (new Market())->fullTimeResult();
        $market->odds()->save(factory(MarketOdd::class)->make([
            'match_id' => $match->getKey(),
            'is_suspended' => true,
            'is_live' => true
        ]));
        $selection = $market->odds()->first();

        $payload = [
            'selections' => [
                [
                    'id' => $selection->id,
                    'match_id' => $match->getKey(),
                    'odds' => $selection->odds
                ],
            ]
        ];

        $response = $this->postJson($this->updateRoute(), $payload);

        $response->assertSuccessful();

        $selection->fresh();

        $this->assertEquals($response->json('data.0.odds'), $selection->odds);
        $this->assertFalse($response->json('data.0.changed'));
        $this->assertTrue($response->json('data.0.suspended'));
        $this->assertTrue($response->json('data.0.available'));
    }

    public function statusToBeUnavailable(): array
    {
        return [
            [Event::STATUS_TO_BE_FIXED],
            [Event::STATUS_ENDED],
            [Event::STATUS_POSTPONED],
            [Event::STATUS_CANCELLED],
            [Event::STATUS_WALKOVER],
            [Event::STATUS_INTERRUPTED],
            [Event::STATUS_ABANDONED],
            [Event::STATUS_RETIRED],
            [Event::STATUS_REMOVED],
        ];
    }

    /**
     * @param $status
     *
     * @dataProvider statusToBeUnavailable
     */
    public function testWillSetASelectionAsUnavailableWhenAEventIsNotLiveOrNotStarted($status)
    {
        $match = factory(Event::class)->create([
            'time_status' => $status
        ]);

        $market = (new Market())->fullTimeResult();
        $market->odds()->save(factory(MarketOdd::class)->make([
            'match_id' => $match->getKey(),
        ]));
        $market->save();
        $selection = $market->odds()->first();
        $selection->update();

        $payload = [
            'selections' => [
                [
                    'id' => $selection->id,
                    'match_id' => $match->getKey(),
                    'odds' => $selection->odds
                ],
            ]
        ];

        $response = $this->postJson($this->updateRoute(), $payload);

        $response->assertSuccessful();
        // Assert that the odds have changed and the the market is not suspended
        $selection->fresh();
        $this->assertEquals($response->json('data.0.odds'), $selection->odds);
        $this->assertFalse($response->json('data.0.changed'));
        $this->assertFalse($response->json('data.0.suspended'));
        $this->assertFalse($response->json('data.0.available'));
    }

    public function testWillSetASelectionAsUnavailableWhenAEventIsLiveAndTheSelectionIsNotLive()
    {
        $match = factory(Event::class)->create([
            'time_status' => Event::STATUS_IN_PLAY
        ]);

        $market = (new Market())->fullTimeResult();
        $market->odds()->save(factory(MarketOdd::class)->make([
            'match_id' => $match->getKey(),
            'is_live' => false
        ]));
        $selection = $market->odds()->first();

        $payload = [
            'selections' => [
                [
                    'id' => $selection->id,
                    'match_id' => $match->getKey(),
                    'odds' => $selection->odds
                ],
            ]
        ];

        $response = $this->postJson($this->updateRoute(), $payload);

        $response->assertSuccessful();
        $selection->fresh();

        $this->assertFalse($response->json('data.0.available'));

        $this->assertEquals($response->json('data.0.odds'), $selection->odds);
        $this->assertFalse($response->json('data.0.changed'));

        $this->assertFalse($response->json('data.0.suspended'));
    }

    public function testShouldRequireAnArrayOfSelections()
    {
        $response = $this->postJson($this->updateRoute(), []);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'selections' => 'The selections field is required'
        ]);
    }

    public function testShouldRequireASelectionThatExistsOnTheDatabase()
    {
        $response = $this->postJson($this->updateRoute(), [
            'selections' => [
                [
                    'id' => $this->faker->randomNumber(2)
                ]
            ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'selections.0.id' => 'The selected selections.0.id is invalid.'
        ]);
    }

    public function testShouldRequireAMatchThatExistsOnTheDatabase()
    {
        $response = $this->postJson($this->updateRoute(), [
            'selections' => [
                [
                    'match_id' => $this->faker->randomNumber(2)
                ]
            ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'selections.0.match_id' => 'The selected selections.0.match_id is invalid.'
        ]);
    }

    public function testShouldRequireAllTheNeededSelectionDetails()
    {
        $response = $this->postJson($this->updateRoute(), [
            'selections' => [
                []
            ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'selections.0.id' => 'The selections.0.id field is required.',
            'selections.0.match_id' => 'The selections.0.match_id field is required.',
            'selections.0.odds' => 'The selections.0.odds field is required.',
        ]);
    }
}
