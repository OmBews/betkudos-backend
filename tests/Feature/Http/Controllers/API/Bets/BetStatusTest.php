<?php

namespace Tests\Feature\Http\Controllers\API\Bets;

use App\Models\Bets\Bet;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Passport\Passport;
use Tests\TestCase;

class BetStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function openBetsRoute()
    {
        return route('bets.open');
    }

    protected function settledBetsRoute()
    {
        return route('bets.settled');
    }

    public function testUserCanGetOpenBetsPaginated()
    {
        $user = factory(User::class)->create();
        $amount = 10;
        $user->bets()->saveMany(
            factory(Bet::class, $amount)->make([
                'status' => Bet::STATUS_OPEN
            ])
        );

        Passport::actingAs($user);

        $response = $this->getJson($this->openBetsRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(8, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function testUserCanNotGetOpenBetsIfTheyAreLoggedOut()
    {
        $response = $this->getJson($this->openBetsRoute());

        $response->assertStatus(401);
    }

    public function testUserCanGetSettledBetsPaginated()
    {
        $user = factory(User::class)->create();
        $amount = 10;
        $user->bets()->saveMany(
            factory(Bet::class, $amount)->make([
                'status' => Bet::STATUS_LOST
            ])
        );

        Passport::actingAs($user);

        $response = $this->getJson($this->settledBetsRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(8, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function testUserCanNotGetSettledBetsIfTheyAreLoggedOut()
    {
        $response = $this->getJson($this->settledBetsRoute());

        $response->assertStatus(401);
    }
}
