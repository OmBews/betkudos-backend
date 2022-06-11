<?php

namespace Tests\Feature\Http\Controllers\API\Auth;

use App\Models\Users\User;
use App\Services\Google2FAService;
use Illuminate\Auth\Passwords\PasswordBroker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Tests\TestCase;

class ResetPasswordControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function resetPasswordRoute(string $method = 'email')
    {
        return route('api.reset-password', ['method' => $method]);
    }

    public function testUserCanResetPasswordWithEmail()
    {
        $user = factory(User::class)->create([
            'username' => 'johndoe'
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson($this->resetPasswordRoute(), [
            'email' => $user->email,
            'password' => $password = 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'token' => $token
        ]);

        $user->refresh();

        $response->assertSuccessful();
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function testCanNotResetPasswordWithEmailIfUserNotExists()
    {
        $token = $this->faker->randomNumber(6);

        $response = $this->postJson($this->resetPasswordRoute(), [
            'email' => $this->faker->email,
            'password' => $password = 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'token' => $token
        ]);

        $response->assertNotFound();
    }

    public function testUserCanNotResetPasswordWithEmailIfTokenIsInvalid()
    {
        $user = factory(User::class)->create([
            'username' => 'johndoe'
        ]);

        $token = Str::random(10);

        $response = $this->postJson($this->resetPasswordRoute(), [
            'email' => $user->email,
            'password' => $password = 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'token' => $token
        ]);

        $user->refresh();

        $response->assertStatus(400);
        $this->assertFalse(Hash::check($password, $user->password));
    }

    public function testShouldRequireTokenToResetPasswordWithEmail()
    {
        $response = $this->postJson($this->resetPasswordRoute(), [
            'email' => $this->faker->email,
            'password' => 'SecretPassword',
            'password_confirmation' => 'SecretPassword'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'token' => 'The token field is required.'
        ]);
    }

    public function testShouldRequireUserEmailToResetPasswordWithEmail()
    {
        $response = $this->postJson($this->resetPasswordRoute(), [
            'password' => 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'token' => $this->faker->randomLetter
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'email' => 'The email field is required.'
        ]);
    }

    public function testShouldRequireNewPasswordToResetItWithEmail()
    {
        $response = $this->postJson($this->resetPasswordRoute(), [
            'email' => $this->faker->email,
            'token' => $this->faker->randomLetter
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password field is required.'
        ]);
    }

    public function testShouldRequireNewPasswordConfirmationToResetItWithEmail()
    {
        $response = $this->postJson($this->resetPasswordRoute(), [
            'email' => $this->faker->email,
            'password' => 'SomePasswor3',
            'token' => $this->faker->randomLetter
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password confirmation does not match.'
        ]);
    }

    public function testUserCanResetYourPasswordWith2FA()
    {
        $OTP = 123456;
        $user = factory(User::class)->create([
            'username' => 'johndoe',
            'google2fa_enabled' => 1
        ]);

        $this->mock(Google2FAService::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('checkOTP')
                ->once()
                ->with($user->google2fa_secret, $OTP)
                ->andReturn(true);
        });

        $response = $this->postJson($this->resetPasswordRoute('2FA'), [
            'email' => $user->email,
            'password' => $password = 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'one_time_password' => $OTP
        ]);

        $user->refresh();

        $response->assertSuccessful();
        $response->assertJson([
            'message' => trans(Password::PASSWORD_RESET)
        ]);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function testUserCaNotResetWith2faDisabled()
    {
        $user = factory(User::class)->create([
            'google2fa_enabled' => 0
        ]);

        $response = $this->postJson($this->resetPasswordRoute('2FA'), [
            'email' => $user->email,
            'password' => $password = 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'one_time_password' => 123456
        ]);

        $response->assertForbidden();
        $this->assertFalse(Hash::check($password, $user->password));
        $response->assertJson([
            'message' => trans('passwords.2fa_disabled')
        ]);
    }

    public function testCaNotResetPasswordWith2faForAUserThatNotExists()
    {
        $response = $this->postJson($this->resetPasswordRoute('2FA'), [
            'email' => $this->faker->email,
            'password' => 'SecretPasswor3',
            'password_confirmation' => 'SecretPasswor3',
            'one_time_password' => 123456
        ]);

        $response->assertNotFound();
        $response->assertJson([
            'message' => trans('passwords.user')
        ]);
    }


    public function testUserCanNotResetPasswordWith2FAIfAnInvalidOtpIsProvided()
    {
        $OTP = $this->faker->randomNumber(6);
        $user = factory(User::class)->create([
            'username' => 'johndoe',
            'google2fa_enabled' => 1
        ]);

        $this->mock(Google2FAService::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('checkOTP')
                ->once()
                ->with($user->google2fa_secret, $OTP)
                ->andReturn(false);
        });

        $response = $this->postJson($this->resetPasswordRoute('2FA'), [
            'email' => $user->email,
            'password' => $password = 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'one_time_password' => $OTP
        ]);

        $user->refresh();

        $response->assertStatus(400);
        $response->assertJson([
            'message' => trans(Password::INVALID_TOKEN)
        ]);
        $this->assertFalse(Hash::check($password, $user->password));
    }

    public function testShouldRequireOTPToResetPasswordWith2FA()
    {
        $response = $this->postJson($this->resetPasswordRoute('2FA'), [
            'email' => $this->faker->email,
            'password' => 'SecretPassword',
            'password_confirmation' => 'SecretPassword'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'one_time_password' => 'The one time password field is required.'
        ]);
    }

    public function testShouldRequireUserEmailToResetPasswordWith2FA()
    {
        $response = $this->postJson($this->resetPasswordRoute('2FA'), [
            'password' => 'SecretPassword',
            'password_confirmation' => 'SecretPassword',
            'one_time_password' => $this->faker->randomNumber(6)
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'email' => 'The email field is required.'
        ]);
    }

    public function testShouldRequireNewPasswordToResetItWith2FA()
    {
        $response = $this->postJson($this->resetPasswordRoute('2FA'), [
            'email' => $this->faker->email,
            'one_time_password' => $this->faker->randomNumber(6)
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password field is required.'
        ]);
    }

    public function testShouldRequireNewPasswordConfirmationToResetItWith2FA()
    {
        $response = $this->postJson($this->resetPasswordRoute(), [
            'email' => $this->faker->email,
            'password' => 'SomePasswor3',
            'one_time_password' => $this->faker->randomNumber(6)
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password confirmation does not match.'
        ]);
    }
}
