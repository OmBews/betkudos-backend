<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\RequiresPassword;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use PragmaRX\Google2FAQRCode\Google2FA;
use PragmaRX\Google2FAQRCode\QRCode\Chillerlan;
use Tests\TestCase;

class RequiresPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected function homeRoute()
    {
        return route('home');
    }

    public function testCanAllowUserWithPassword()
    {
        $user = factory(User::class)->make([
            'password' => 'Passwor3',
        ]);

        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('user')
                ->once()
                ->andReturn($user);

        $request->shouldReceive('all')
                ->andReturn([]);

        $request->shouldReceive('route')
                ->andReturn($this->homeRoute());

        $request->shouldReceive('validateLogin')
                ->andReturn(true);

        $request->shouldReceive('validate')
                ->andReturn(true);

        $request->password = 'Passwor3';

        $middleware = new RequiresPassword;

        $response = $middleware->handle($request, function () {
            return new Response();
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCanNotAllowUserWithoutPassword()
    {
        $user = factory(User::class)->make();

        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('user')
                ->once()
                ->andReturn($user);

        $request->shouldReceive('all')
                ->andReturn([]);

        $request->shouldReceive('route')
                ->andReturn($this->homeRoute());

        $request->shouldReceive('validateLogin')
                ->andReturn(false);

        $request->shouldReceive('validate')
                ->andReturn(true);

        $middleware = new RequiresPassword;

        $response = $middleware->handle($request, function () {
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(json_decode($response->getContent(), 1), [
            'message' => trans('auth.failed')
        ]);
    }

    public function testCanAllowUserWithAnValidOTP()
    {
        $user = factory(User::class)->make([
            'google2fa_enabled' => 1
        ]);

        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('user')
                ->times(2)
                ->andReturn($user);

        $request->shouldReceive('all')
                ->andReturn([]);

        $request->shouldReceive('route')
                ->andReturn($this->homeRoute());

        $request->shouldReceive('validateLogin')
                ->andReturn(true);

        $request->shouldReceive('validate')
                ->andReturn(true);

        $OTP = 123456;
        $request->one_time_password = $OTP;

        $this->mock(Google2FA::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('setQrCodeService')
                ->once()
                ->andReturnSelf();

            $mock->shouldReceive('verifyKey')
                 ->once()
                 ->with($user->google2fa_secret, $OTP)
                 ->andReturn(true);

        });

        $middleware = new RequiresPassword;

        $response = $middleware->handle($request, function () {
            return new Response();
        });

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testShouldRequireOTPIf2FAIsEnabled()
    {
        $user = factory(User::class)->make([
            'google2fa_enabled' => 1
        ]);

        $request = \Mockery::mock(Request::class);

        $request->shouldReceive('user')
                ->times(2)
                ->andReturn($user);

        $request->shouldReceive('all')
                ->andReturn([]);

        $request->shouldReceive('route')
                ->andReturn($this->homeRoute());

        $request->shouldReceive('validateLogin')
                ->andReturn(false);

        $request->shouldReceive('validate')
                ->andReturn(true);

        $middleware = new RequiresPassword;

        $response = $middleware->handle($request, function () {
        });

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals(json_decode($response->getContent(), 1), [
            'message' => trans('auth.2fa.invalid_otp')
        ]);
    }
}
