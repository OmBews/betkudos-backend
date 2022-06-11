<?php

namespace Tests\Feature\Http\Controllers\API\Auth;

use App\Events\Auth\Logout;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Tests\TestCase;

class LogoutControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function logoutRoute()
    {
        return route('api.logout');
    }

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testUserCanLogout()
    {
        Event::fake([Logout::class]);

        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->postJson($this->logoutRoute());

        $response->assertStatus(200);

        Event::assertDispatched(Logout::class);
    }
}
