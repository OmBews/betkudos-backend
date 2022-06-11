<?php

namespace Tests\Feature\Http\Controllers\API\Auth;

use App\Events\Auth\Login;
use App\Http\Middleware\NotAllowedIpAddress;
use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Users\User;
use App\Services\AuthService;
use App\Services\Google2FAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Request;
use Mockery;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function loginRoute()
    {
        return route('api.login');
    }

    public function testUserCanLoginWithUsername()
    {
        Event::fake([Login::class]);

        $user = factory(User::class)->create(['password' => $password = 'Passwor3']);

        $this->mock(AuthService::class, function ($mock) use ($password, $user) {
            $mock->shouldReceive('requestAccessToken')->once()
                 ->with($user->username, $password);
        });

        $response = $this->postJson($this->loginRoute(), [
            'username' => $user->username,
            'password' => $password,
        ]);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'user',
            'token'
        ]);
        Event::assertDispatched(Login::class);
    }

    public function testUserCanLoginWithEmail()
    {
        Event::fake([Login::class]);
        $user = factory(User::class)->create(['password' => $password = 'Passwor3']);

        $this->mock(AuthService::class, function ($mock) use ($password, $user) {
            $mock->shouldReceive('requestAccessToken')->once()
                 ->with($user->username, $password);
        });

        $response = $this->postJson($this->loginRoute(), [
            'username' => $user->email,
            'password' => $password,
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'user',
            'token'
        ]);
        Event::assertDispatched(Login::class);
    }

    public function testUserCanLoginWith2FA()
    {
        Event::fake([Login::class]);

        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3',
            'google2fa_enabled' => true
        ]);
        $OTP = 123456;

        $this->mock(AuthService::class, function ($mock) use ($password, $user) {
            $mock->shouldReceive('requestAccessToken')->once()
                 ->with($user->username, $password);
        });
        $this->mock(Google2FAService::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('checkOTP')
                 ->once()
                 ->with($user->google2fa_secret, $OTP)
                 ->andReturn(true);
        });

        $response = $this->postJson($this->loginRoute(), [
            'username' => $user->email,
            'password' => $password,
            'one_time_password' => $OTP
        ]);

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'user',
            'token'
        ]);
        Event::assertDispatched(Login::class);
    }

    public function testUserCanNotLoginIfSportsBookIsBlocked()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);

        setting(['global.block_sports_book' => 1])->save();

        $response = $this->postJson($this->loginRoute(), [
            'username' => $user->username,
            'password' => $password,
        ]);

        $response->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $response->assertJson([
            'message' => 'Service unavailable, try again later.'
        ]);
    }

    public function testCanCheckTheUserPassword()
    {
        $user = factory(User::class)->create(['password' => $password = 'Passwor3']);

        $response = $this->postJson($this->loginRoute(), [
            'username' => $user->username,
            'password' => 'differentPassword',
        ]);

        $response->assertStatus(Response::HTTP_UNAUTHORIZED);
        $response->assertJson([
            'message' => trans('auth.failed')
        ]);
        $response->assertJsonStructure([
            'message'
        ]);
    }

    public function testUserCanNotLoginIfYourIpIsBlocked()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);

        factory(NotAllowedIp::class)->create([
            'ip_address' => $user->ip_address
        ]);

        $response = $this->postJson($this->loginRoute(), [
            'username' => $user->username,
            'password' => $password,
        ], [
            'REMOTE_ADDR' => $user->ip_address
        ]);

        $response->assertForbidden();
    }

    public function testUserCanNotLoginIfYourProfileIsRestricted()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3',
            'restricted' => true
        ]);

        $response = $this->postJson($this->loginRoute(), [
            'username' => $user->username,
            'password' => $password,
        ]);

        $response->assertForbidden();

        $response->assertJson([
            'message' => trans('user.restricted')
        ]);
    }

    public function testShouldRequireUsername()
    {
        $response = $this->postJson($this->loginRoute(), [
            'password' => 'Passwor3',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'username' => 'The username field is required.'
        ]);
    }

    public function testShouldRequirePassword()
    {
        $response = $this->postJson($this->loginRoute(), [
            'username' => 'johndoe',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password field is required.'
        ]);
    }

    public function testShouldRequire2faIfItIsEnabled()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3',
            'google2fa_enabled' => 1
        ]);

        $response = $this->postJson($this->loginRoute(), [
           'username' => $user->username,
           'password' => $password,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => trans('auth.2fa.invalid_otp')
        ]);
    }
}
