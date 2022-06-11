<?php

namespace Tests\Feature\Http\Controllers\API\Auth;

use App\Models\Users\User;
use App\Notifications\Auth\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function sendResetLinkEmailRoute()
    {
        return route('api.forgot-password');
    }

    public function testCanSendResetLinkEmail()
    {
        Notification::fake();

        $user = factory(User::class)->create();

        $response = $this->postJson($this->sendResetLinkEmailRoute(), [
            'email' => $user->email
        ]);

        $response->assertSuccessful();

        Notification::assertSentTo(
            $user,
            ResetPassword::class
        );
        $response->assertJson([
            'message' => trans('passwords.sent')
        ]);
    }

    public function testShouldRequireUserEmail()
    {
        Notification::fake();

        $response = $this->postJson($this->sendResetLinkEmailRoute());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        Notification::assertNothingSent();
    }

    public function testShouldRecommendToUse2FA()
    {
        Notification::fake();

        $user = factory(User::class)->create([
            'google2fa_enabled' => 1
        ]);

        $response = $this->postJson($this->sendResetLinkEmailRoute(), [
            'email' => $user->email
        ]);

        $response->assertStatus(Response::HTTP_FORBIDDEN);
        $response->assertJson([
            'message' => trans('passwords.reset_2fa', [ 'username' => $user->username ]),
            'requester' => [
                'username' => $user->username,
                'email' => $user->email
            ]
        ]);
        Notification::assertNothingSent();
    }

    public function testShouldNotSendAnResetLinkEmailIfUserDoesNotExists()
    {
        Notification::fake();

        $response = $this->postJson($this->sendResetLinkEmailRoute(), [
            'email' => 'some_mail@mail.com'
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        Notification::assertNothingSent();
        $response->assertJson([
            'message' => trans('passwords.user')
        ]);
    }
}
