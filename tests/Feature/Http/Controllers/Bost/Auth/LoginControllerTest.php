<?php

namespace Tests\Feature\Http\Controllers\Bost\Auth;

use App\Events\Auth\Login;
use App\Models\Users\User;
use App\Services\AuthService;
use App\Services\Google2FAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\RolesAndPermissionsSeeder::class);
    }

    protected function bostLoginRoute()
    {
        return route('bost.login');
    }

    public function testUserCanLoginWithUsername()
    {
        Event::fake([Login::class]);

        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);
        $user->assignRole('bookie');
        $user->enable2FA();

        $params = [
            'username' => $user->username,
            'password' => $password,
            'one_time_password' => $OTP = 123456
        ];

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

        $response = $this->postJson($this->bostLoginRoute(), $params);

        Event::assertDispatched(Login::class);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'user'
        ]);
    }

    public function testUserCanLoginEvenIfSportsBookIsBlocked()
    {
        setting(['global.block_sports_book' => 1])->save();

        $this->testUserCanLoginWithUsername();
    }

    public function testUserCanLoginWithEmail()
    {
        Event::fake([Login::class]);

        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);
        $user->assignRole('bookie');
        $user->enable2FA();

        $params = [
            'username' => $user->email,
            'password' => $password,
            'one_time_password' => $OTP = 123456
        ];

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

        $response = $this->postJson($this->bostLoginRoute(), $params);

        Event::assertDispatched(Login::class);
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'token',
            'user'
        ]);
    }

    public function testUserCanNotLoginIfDoNotHaveBookieRole()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);
        $user->enable2FA();

        $params = [
            'username' => $user->username,
            'password' => $password,
            'one_time_password' => $OTP = 123456
        ];

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

        $response = $this->postJson($this->bostLoginRoute(), $params);

        $response->assertForbidden();
        $response->assertJson([
            'message' => trans('auth.forbidden')
        ]);
    }

    public function testUserCanNotLoginWith2faDisabled()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);
        $user->disable2FA();

        $params = [
            'username' => $user->email,
            'password' => $password,
            'one_time_password' => $OTP = 123456
        ];

        $response = $this->postJson($this->bostLoginRoute(), $params);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => trans('auth.2fa.disabled')
        ]);
    }

    public function testBookieCanNotLoginWithoutOtpCode()
    {
        $params = [
            'username' => $this->faker->userName,
            'password' => 'Passwor3'
        ];

        $response = $this->postJson($this->bostLoginRoute(), $params);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'one_time_password' => 'The one time password field is required.'
        ]);
    }
}
