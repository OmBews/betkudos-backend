<?php

namespace Tests\Feature\Http\Controllers\API\Auth;

use App\Events\Auth\Google2faDisabled;
use App\Events\Auth\Google2faEnabled;
use App\Models\Users\User;
use App\Services\Google2FAService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Passport;
use Mockery\Generator\StringManipulation\Pass\Pass;
use Tests\TestCase;

class Google2FAControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function QRCodeRoute()
    {
        return route('api.2fa.qr-code');
    }

    protected function enable2FARoute()
    {
        return route('api.2fa.enable');
    }

    protected function disable2FARoute()
    {
        return route('api.2fa.disable');
    }

    public function testCanRetrieveUserQrCode()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->getJson($this->QRCodeRoute());

        $response->assertSuccessful();
        $response->assertJsonStructure([
            '_2FA' => [
                'QRCode',
                'google2fa_secret'
            ]
        ]);
    }

    public function testCaNotRetrieveQRCodeIf2FAIsEnabled()
    {
        $user = factory(User::class)->create([
            'google2fa_enabled' => 1
        ]);

        Passport::actingAs($user);

        $response = $this->getJson($this->QRCodeRoute());

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => trans('auth.2fa.already_enabled')
        ]);
    }

    public function testCanEnable2FA()
    {
        Event::fake([Google2faEnabled::class]);
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $OTP = 123456;

        $this->mock(Google2FAService::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('checkOTP')
                 ->once()
                 ->with($user->google2fa_secret, $OTP)
                 ->andReturn(true);
        });

        $response = $this->postJson($this->enable2FARoute(), [
            'one_time_password' => $OTP
        ]);
        Event::assertDispatched(Google2faEnabled::class);
        $response->assertSuccessful();
        $this->assertEquals(1, $user->google2fa_enabled);
    }

    public function testUserCanNotEnable2FAWhenItIsAlreadyEnabled()
    {
        $user = factory(User::class)->create([
            'google2fa_enabled' => 1
        ]);

        Passport::actingAs($user);

        $OTP = 123456;

        $response = $this->postJson($this->enable2FARoute(), [
            'one_time_password' => $OTP
        ]);

        $response->assertStatus(Response::HTTP_BAD_REQUEST);
        $response->assertJson([
            'message' => trans('auth.2fa.already_enabled')
        ]);
    }

    public function testShouldCheckIfTheOTPIsInvalid()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $OTP = 123456;

        $this->mock(Google2FAService::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('checkOTP')
                ->once()
                ->with($user->google2fa_secret, $OTP)
                ->andReturn(false); // Invalid OTP
        });

        $response = $this->postJson($this->enable2FARoute(), [
            'one_time_password' => $OTP
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => trans('auth.2fa.invalid_otp')
        ]);
    }

    public function testShouldRequireOTPCodeToEnable2FA()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->postJson($this->enable2FARoute());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'one_time_password' => 'The one time password field is required.'
        ]);
    }

    public function testCanDisable2FA()
    {
        Event::fake([Google2faDisabled::class]);
        $user = factory(User::class)->create([
            'google2fa_enabled' => 1
        ]);

        Passport::actingAs($user);

        $OTP = 123456;

        $this->mock(Google2FAService::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('checkOTP')
                 ->once()
                 ->with($user->google2fa_secret, $OTP)
                 ->andReturn(true);
        });
        $response = $this->postJson($this->disable2FARoute(), [
            'one_time_password' => $OTP
        ]);

        $user->refresh();

        Event::assertDispatched(Google2faDisabled::class);
        $response->assertSuccessful();
        $response->assertJson([
            'message' => trans('passwords.2fa_disabled')
        ]);
        $response->assertJsonStructure([
            'message',
            'user'
        ]);
    }

    public function testShouldRequire2faOtpCodeToDisable2FA()
    {
        $user = factory(User::class)->create([
            'google2fa_enabled' => 1
        ]);

        Passport::actingAs($user);

        $response = $this->postJson($this->disable2FARoute());

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'one_time_password' => 'The one time password field is required.'
        ]);
    }
}
