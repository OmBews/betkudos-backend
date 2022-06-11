<?php

namespace Tests\Feature\Http\Controllers\API\Auth;

use App\Models\Users\User;
use App\Notifications\Auth\EmailVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Laravel\Passport\Passport;
use Tests\TestCase;

class VerificationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function verifyRoute(array $params)
    {
        return route('api.verify-email', $params);
    }

    protected function resendRoute()
    {
        return route('api.resend-verification');
    }

    public function testCanVerifyAnUser()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);
        $routeParams = [ 'id' => $user->id, 'hash' => sha1($user->email) ];

        $response = $this->getJson(URL::temporarySignedRoute(
            'api.verify-email',
            now()->addHour(),
            $routeParams
        ));

        $user->refresh();

        $response->assertRedirect();
        $this->assertTrue($user->hasVerifiedEmail());
    }

    public function testCanResendAVerificationLink()
    {
        Notification::fake();

        $user = factory(User::class)->create(['email_verified_at' => null]);

        Passport::actingAs($user);

        $response = $this->postJson($this->resendRoute(), [
            'email' => $user->email
        ]);

        $user->refresh();

        Notification::assertSentTo(
            $user,
            EmailVerification::class
        );
        $this->assertFalse($user->hasVerifiedEmail());
        $response->assertSuccessful();
    }

    public function testShouldRequireAnEmailAddressToResendVerification()
    {
        $user = factory(User::class)->create(['email_verified_at' => null]);

        Passport::actingAs($user);

        $response = $this->postJson($this->resendRoute());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'email' => 'The email field is required.'
        ]);
    }
}
