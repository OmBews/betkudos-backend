<?php

namespace Tests\Feature\Http\Controllers\API\Auth;

use App\Events\Auth\Registered;
use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use App\Notifications\Auth\EmailVerification;
use App\Services\AuthService;
use Database\Seeders\CryptoCurrenciesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Mockery;
use Tests\TestCase;

class RegisterControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $username;
    protected $password;
    protected $email;
    protected $form;
    /**
     * @var array
     */
    private $authTokenResponse;

    public function __construct()
    {
        parent::__construct();
        $this->email = 'avalidemail@example.com';
        $this->password = 'SecretPassword123';
        $this->username = 'johndoe';
        $this->form = [
            'username' => $this->username,
            'password' => $this->password,
            'password_confirmation' => $this->password,
            'email' => $this->email
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Artisan::call('passport:install');

        $this->seed(CryptoCurrenciesTableSeeder::class);
    }

    protected function registerRoute()
    {
        return route('api.register');
    }

    public function testUserCanRegister()
    {
        Notification::fake();
        Event::fake([Registered::class]);

        $this->instance(AuthService::class, Mockery::mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('requestAccessToken')->once()
                 ->with($this->username, $this->password);
        }));

        $response = $this->postJson($this->registerRoute(), $this->form);

        $user = User::latest()->with('wallets')->first();

        $response->assertCreated();

        Notification::assertSentTo(
            $user,
            EmailVerification::class
        );

        Event::assertDispatched(Registered::class);

        $response->assertJsonStructure([
            'user',
            'token',
        ]);
        $response->assertJsonMissing(['message']);
        $response->assertJsonMissingValidationErrors(['username', 'email', 'password']);
        $this->assertEquals($this->username, $user->username);
        $this->assertEquals($this->email, $user->email);
        $this->assertNotEquals($this->password, $user->password);
        $this->assertTrue(Hash::check($this->password, $user->password));
        $this->assertCount(2, $user->wallets);
    }

    public function testUserCanNotRegisterIfSportsBookIsBlocked()
    {
        setting(['global.block_sports_book' => 1])->save();

        $response = $this->postJson($this->registerRoute());

        $response->assertStatus(Response::HTTP_SERVICE_UNAVAILABLE);
        $response->assertJson([
            'message' => 'Service unavailable, try again later.'
        ]);
    }

    public function testUserCanNotRegisterWithoutAnEmail()
    {
        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonMissing(['message']);
        $response->assertJsonMissingValidationErrors([
            'username',
            'password',
        ]);
        $this->assertTrue($user === null);
    }

    public function testUserCanNotRegisterIfYourIpIsBlocked()
    {
        factory(NotAllowedIp::class)->create([
            'ip_address' => $ipAddress = $this->faker->ipv4
        ]);

        $response = $this->postJson($this->registerRoute(),
            [
                'username' => $this->username,
                'password' => $this->password,
                'password_confirmation' => $this->password,
            ],
            [
                'REMOTE_ADDR' => $ipAddress
            ]
        );

        $response->assertForbidden();
        $this->assertDatabaseMissing('users', [
            'username' => $this->username
        ]);
    }

    public function testShouldNotSendAEmailVerificationIfAErrorOccurs()
    {
        Notification::fake();
        // Mocking a Exception from AuthService
        $this->instance(AuthService::class, Mockery::mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('requestAccessToken')->once()
                ->with($this->username, $this->password)
                ->andThrow(\Exception::class, 'Error');
        }));

        $response = $this->postJson($this->registerRoute(), $this->form);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        Notification::assertNothingSent();
    }

    public function testShouldDeleteTheUserIfAuthServiceThrowsAnException()
    {
        Notification::fake();
        // Mocking a Exception from AuthService
        $this->instance(AuthService::class, Mockery::mock(AuthService::class, function ($mock) {
            $mock->shouldReceive('requestAccessToken')->once()
                ->with($this->username, $this->password)
                ->andThrow(\Exception::class, 'Error');
        }));

        $response = $this->postJson($this->registerRoute(), $this->form);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $this->assertTrue($user === null);
    }

    public function testItShouldRequireAPassword()
    {
        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password field is required.'
        ]);
        $response->assertJsonMissingValidationErrors(['username', 'email']);
        $this->assertTrue($user === null);
    }

    public function testItShouldRequireAPasswordConfirmation()
    {
        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('password');
        $response->assertJsonMissingValidationErrors([
            'username',
            'email',
        ]);
        $this->assertTrue($user === null);
    }

    public function testEmailShouldBeValidWhenIsSupplied()
    {
        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => 'invalid_email.com',
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'email' => 'The email must be a valid email address.'
        ]);
        $response->assertJsonMissingValidationErrors([
            'username',
            'password',
        ]);
        $this->assertTrue($user === null);
    }

    public function testUsernameCanNotBeGreaterThan14Characters()
    {
        $response = $this->postJson($this->registerRoute(), [
            'username' => Str::random(15),
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'username' => 'The username may not be greater than 14 characters.'
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'password',
        ]);
        $this->assertTrue($user === null);
    }

    public function testUsernameCanNotBeLessThan6Characters()
    {
        $response = $this->postJson($this->registerRoute(), [
            'username' => $username = 'johnd',
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $user = User::where('username', $username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors('username');
        $response->assertJsonMissingValidationErrors([
            'email',
            'password',
        ]);
        $this->assertTrue($user === null);
    }

    public function testUsernameShouldBeAlphaNumeric()
    {
        $response = $this->postJson($this->registerRoute(), [
            'username' => 'john_doe@123',
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'username' => 'The username may only contain letters and numbers.'
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'password',
        ]);
        $this->assertTrue($user === null);
    }

    public function testPasswordMayNotBeGreaterThan28Characters()
    {
        $password = '1RandomPasswordGreaterThan28Chars';

        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password may not be greater than 28 characters.'
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'username',
        ]);
        $this->assertTrue($user === null);
    }

    public function testPasswordMustBeAtLeast8Characters()
    {
        $password = '4Passwd';

        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password must be at least 8 characters.'
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'username',
        ]);
        $this->assertTrue($user === null);
    }

    public function testPasswordShouldIncludeAtLeastOneUppercase()
    {
        $password = 'p4ssw0r3lower';

        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => trans('validation.custom.password.uppercase')
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'username',
        ]);
        $this->assertTrue($user === null);
    }

    public function testPasswordShouldIncludeAtLeastOneLowercase()
    {
        $password = 'P4SSW0R3UPPER';

        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => trans('validation.custom.password.lowercase')
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'username',
        ]);
        $this->assertTrue($user === null);
    }

    public function testPasswordShouldIncludeAtLeastOneNumber()
    {
        $password = 'PasswordWithoutANumber';

        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => trans('validation.custom.password.number')
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'username',
        ]);
        $this->assertTrue($user === null);
    }

    public function testPasswordShouldContainMaximumHalfOfUsername()
    {
        $password = 'johndoePassword123';

        $response = $this->postJson($this->registerRoute(), [
            'username' => $this->username,
            'email' => $this->email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $user = User::where('username', $this->username)->first();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => trans('validation.custom.password.matches_username')
        ]);
        $response->assertJsonMissingValidationErrors([
            'email',
            'username',
        ]);
        $this->assertTrue($user === null);
    }
}
