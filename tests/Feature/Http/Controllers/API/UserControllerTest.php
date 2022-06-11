<?php

namespace Tests\Feature\Http\Controllers\API;

use App\Contracts\Repositories\UserRepository;
use App\Events\Auth\EmailUpdated;
use App\Events\Auth\PasswordUpdated;
use App\Http\Controllers\API\UserController;
use App\Http\Resources\WalletResource;
use App\Models\Currencies\CryptoCurrency;
use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Sessions\Session;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use App\Notifications\Auth\AccountChanges;
use App\Notifications\Auth\EmailVerification;
use App\Services\AuthService;
use App\Services\Google2FAService;
use App\Services\UserService;
use Database\Seeders\CryptoCurrenciesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\RolesAndPermissionsSeeder::class);
        $this->seed(CryptoCurrenciesTableSeeder::class);
    }

    public function indexRoute()
    {
        return route('users.index');
    }

    public function accountRoute()
    {
        return route('user.account');
    }

    public function searchRoute($value, $field, $filter = UserRepository::DEFAULT_FILTER)
    {
        return route('users.search', [
            'value' => $value,
            'field' => $field,
            'filter' => $filter
        ]);
    }

    public function filterRoute($type)
    {
        return route('users.filter', ['type' => $type]);
    }

    public function restrictUserRoute(User $user)
    {
        return route('users.restrict', ['id' => $user->getKey()]);
    }

    public function blockCurrentIpRoute(User $user)
    {
        return route('users.block-current-ip', ['user' => $user->getKey()]);
    }

    public function updateRoute()
    {
        return route('user.update');
    }

    // List users

    public function testUserCanListUsers()
    {
        factory(User::class, 9)->create();

        $bookie = factory(User::class)->create()
                ->assignRole('bookie');
        Passport::actingAs($bookie);

        $response = $this->getJson($this->indexRoute());

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data'
        ]);
        $response->assertJsonCount(10, 'data');
    }

    public function testUserCanNotListUsersWithoutPermission()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->getJson($this->indexRoute());

        $response->assertForbidden();
    }

    public function testListOfUsersShouldBePaginated()
    {
        factory(User::class, User::PER_PAGE * 2)->create();

        $bookie = factory(User::class)->create()
                ->assignRole('bookie');
        Passport::actingAs($bookie);

        $response = $this->getJson($this->indexRoute());

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
        $response->assertJsonCount(User::PER_PAGE, 'data');
    }

    public function testCanRetrieveListOfUsersForEachPage()
    {
        $bookie = factory(User::class)->create()
                ->assignRole('bookie');
        Passport::actingAs($bookie);

        $pages = 4;
        factory(User::class, $pages * User::PER_PAGE)->create();

        for ($i = 0; $i < $pages; $i++) {
            $page = $i + 1;
            $response = $this->getJson($this->indexRoute() . "?page=$page");

            $response->assertSuccessful();
            $response->assertJsonStructure([
                'data',
                'links',
                'meta'
            ]);
            $response->assertJsonCount(User::PER_PAGE, 'data');
        }
    }

    // User account

    public function testUserCanGetAccountDetails()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => null
        ]);

        Passport::actingAs($user);

        $response = $this->getJson($this->accountRoute());

        $response->assertSuccessful();
        $response->assertExactJson([
            'data' => [
                'username' => $user->username,
                'balance' => $user->balance,
                'google2fa_enabled' => $user->google2fa_enabled,
                'email_verified_at' => null,
                'wallets' => [],
            ]
        ]);
    }

    public function testUserCanGetAccountDetailsWithTheirWallets()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => null
        ]);

        Passport::actingAs($user);

        $wallets = [];

        foreach (CryptoCurrency::all()->take(2) as $currency) {
            $wallets[] = Wallet::factory()->create(['user_id' => $user->getKey(), 'crypto_currency_id' => $currency->getKey()]);
        }

        $wallets = collect($wallets);

        $response = $this->getJson($this->accountRoute());

        $response->assertSuccessful();
        $response->assertJson([
            'data' => [
                'username' => $user->username,
                'balance' => $user->balance,
                'google2fa_enabled' => $user->google2fa_enabled,
                'email_verified_at' => null,
                'wallets' => json_decode(WalletResource::collection($wallets)->toJson(), true),
            ]
        ]);
    }

    public function testWillNotReturnEmailVerifiedAtFieldIfUserAlreadyHaveVerifiedYourEmail()
    {
        $user = factory(User::class)->create([
            'email_verified_at' => now()
        ]);

        Passport::actingAs($user);

        $response = $this->getJson($this->accountRoute());

        $response->assertSuccessful();
        $response->assertExactJson([
            'data' => [
                'username' => $user->username,
                'balance' => $user->balance,
                'google2fa_enabled' => $user->google2fa_enabled,
                'wallets' => [],
            ]
        ]);
    }

    public function testUserCanNotGetAccountDetailsIfTheyAreLoggedOut()
    {
        $this->getJson($this->accountRoute())
             ->assertStatus(401);
    }

    // Search users

    public function testUserCanSearchUsersById()
    {
        $bookie = factory(User::class)
                    ->create()
                    ->assignRole('bookie');
        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $response = $this->getJson(
            $this->searchRoute($user->id, UserController::SEARCH_FIELD_ID)
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data'
        ]);
        $this->assertEquals($user->id, $response->json('data.0.id'));
        $this->assertEquals($user->email, $response->json('data.0.email'));
    }

    public function testUserCanSearchUsersByIpAddress()
    {
        $bookie = factory(User::class)
                    ->create()
                    ->assignRole('bookie');
        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $response = $this->getJson(
            $this->searchRoute($user->ip_address, UserController::SEARCH_FIELD_IP)
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data'
        ]);
        $this->assertEquals($user->id, $response->json('data.0.id'));
        $this->assertEquals($user->email, $response->json('data.0.email'));
        $this->assertEquals($user->ip_address, $response->json('data.0.ipAddress'));
    }

    public function testUserCanSearchUsersByIpAddressUsingLike()
    {
        $bookie = factory(User::class)
                    ->create()
                    ->assignRole('bookie');
        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $response = $this->getJson(
            $this->searchRoute(
                substr($user->ip_address, 0, 5),
                UserController::SEARCH_FIELD_IP
            )
        );

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data'
        ]);
        $this->assertEquals($user->id, $response->json('data.0.id'));
        $this->assertEquals($user->email, $response->json('data.0.email'));
        $this->assertEquals($user->ip_address, $response->json('data.0.ipAddress'));
    }

    public function testUserCanSearchUsersByUsername()
    {
        $bookie = factory(User::class)->create()
                ->assignRole('bookie');
        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $response = $this->getJson($this->searchRoute(
            $user->username,
            UserController::SEARCH_FIELD_USERNAME
        ));

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data'
        ]);
        $this->assertEquals($user->id, $response->json('data.0.id'));
        $this->assertEquals($user->email, $response->json('data.0.email'));
        $this->assertEquals($user->username, $response->json('data.0.username'));
    }

    public function testReturnNotFoundSearchingUserThatDoesNotExistsById()
    {
        $bookie = factory(User::class)->create()
                ->assignRole('bookie');
        Passport::actingAs($bookie);

        $response = $this->getJson($this->searchRoute(
            2,
            UserController::SEARCH_FIELD_ID
        ));

        $response->assertNotFound();
        $response->assertJsonStructure([
            'message'
        ]);
    }

    public function testReturnNotFoundSearchingUserThatDoesNotExistsByIP()
    {
        $bookie = factory(User::class)->create()
                ->assignRole('bookie');
        Passport::actingAs($bookie);

        $response = $this->getJson($this->searchRoute('127.0.0.1', 'IP'));

        $response->assertNotFound();
        $response->assertJsonStructure([
            'message'
        ]);
    }

    public function testReturnNotFoundSearchingUserThatDoesNotExistsByUsername()
    {
        $bookie = factory(User::class)->create()
                ->assignRole('bookie');
        Passport::actingAs($bookie);

        $response = $this->getJson($this->searchRoute('Some username', 'username'));

        $response->assertNotFound();
        $response->assertJsonStructure([
            'message'
        ]);
    }

    public function testUserCanNotSearchUsersWithoutPermission()
    {
        $bookie = factory(User::class)->create();

        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $response = $this->getJson($this->searchRoute(
            $user->id,
            UserController::SEARCH_FIELD_ID
        ));

        $response->assertForbidden();
    }

    // filter

    public function testUserCanFilterUsersByStatus()
    {
        $bookie = factory(User::class)->create()
                ->assignRole('bookie');

        Passport::actingAs($bookie);

        factory(User::class, 13)->create();
        $restricted = factory(User::class)->create([
            'restricted' => 1
        ]);

        $response = $this->getJson($this->filterRoute(UserRepository::FILTER_STATUS));

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
        $response->assertJsonCount(15, 'data');
        // Ordering status by 'DESC'
        $this->assertEquals($restricted->id, $response->json('data.0.id'));
    }

    public function testUserCanFilterUsersByAllUsersFilter()
    {
        $bookie = factory(User::class)
                    ->create()
                    ->assignRole('bookie');

        Passport::actingAs($bookie);

        factory(User::class, 14)->create();

        $response = $this->getJson($this->filterRoute(UserRepository::FILTER_ALL_USERS));

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
        $response->assertJsonCount(15, 'data');
    }

    public function testShouldValidateTheFilter()
    {
        $bookie = factory(User::class)->create()
                ->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->filterRoute('invalid filter'));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testUserCanNotFilterWithoutPermission()
    {
        $bookie = factory(User::class)->create();

        Passport::actingAs($bookie);

        $response = $this->getJson($this->filterRoute('some filter'));

        $response->assertForbidden();
    }

    // Restrict/Unrestrict users

    public function testBookieCanRestrictAUser()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create([
           'restricted' => false
        ]);

        $token = factory(Token::class)->create([
            'user_id' => $user->id
        ]);

        factory(Session::class)->create([
            'oauth_access_token_id' => $token->id,
            'user_id' => $user->id,
            'device_id' => $this->faker->randomDigit
        ]);

        $response = $this->putJson($this->restrictUserRoute($user), [
            'restrict' => true
        ]);

        $user->refresh();
        $token->refresh();

        $response->assertSuccessful();

        $this->assertEquals(User::STATUS_RESTRICTED, $user->status());
        $this->assertTrue($token->revoked);
    }

    public function testBookieCanUnRestrictAUser()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create([
           'restricted' => true
        ]);

        $response = $this->putJson($this->restrictUserRoute($user), [
            'restrict' => false
        ]);

        $user->refresh();

        $response->assertSuccessful();

        $this->assertEquals(User::STATUS_ACTIVE, $user->status());
    }

    public function testBookieCanNotRestrictUserWithoutPermission()
    {
        $bookie = factory(User::class)->create();

        Passport::actingAs($bookie);

        $user = factory(User::class)->create([
           'restricted' => true
        ]);

        $response = $this->putJson($this->restrictUserRoute($user), [
            'restrict' => false
        ]);

        $user->refresh();

        $response->assertForbidden();

        $this->assertNotEquals(User::STATUS_ACTIVE, $user->status());
    }

    public function testShouldRequireRestrictFieldToUpdateTheUserStatus()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create([
           'restricted' => true
        ]);

        $response = $this->putJson($this->restrictUserRoute($user));

        $user->refresh();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'restrict' => 'The restrict field is required.'
        ]);

        $this->assertNotEquals(User::STATUS_ACTIVE, $user->status());
    }

    public function testShouldValidateRestrictFieldToUpdateTheUserStatus()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create([
           'restricted' => true
        ]);

        $response = $this->putJson($this->restrictUserRoute($user), [
            'restrict' => 'Some invalid data'
        ]);

        $user->refresh();

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'restrict' => 'The restrict field must be true or false.'
        ]);

        $this->assertNotEquals(User::STATUS_ACTIVE, $user->status());
    }

    // Block/Unblock current IP

    public function testBookieCanBlockCurrentUserIpAddress()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $token = factory(Token::class)->create([
            'user_id' => $user->id
        ]);

        $user->sessions()->save(
            factory(Session::class)->make([
                'oauth_access_token_id' => $token->id,
                'device_id' => $this->faker->randomDigit,
                'ip_address' => $user->ip_address
            ])
        );

        $response = $this->putJson($this->blockCurrentIpRoute($user), [
            'block' => true
        ]);

        $response->assertSuccessful();

        $token->refresh();

        $this->assertEquals(1, $token->revoked);
        $this->assertDatabaseHas('not_allowed_ips', [
            'ip_address' => $user->ip_address
        ]);
    }

    public function testBookieCanUnBlockCurrentUserIpAddress()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        factory(NotAllowedIp::class)->create([
            'ip_address' => $user->ip_address
        ]);

        $response = $this->putJson($this->blockCurrentIpRoute($user), [
            'block' => false
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('not_allowed_ips', [
            'ip_address' => $user->ip_address
        ]);
    }

    public function testCanNotBlockCurrentUserIpAddressWithoutPermission()
    {
        $bookie = factory(User::class)->create();

        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $response = $this->putJson($this->blockCurrentIpRoute($user), [
            'block' => true
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('not_allowed_ips', [
            'ip_address' => $user->ip_address
        ]);
    }

    // Update user

    public function testUserCanUpdateDetails()
    {
        Event::fake([
            PasswordUpdated::class,
            EmailUpdated::class
        ]);
        Notification::fake();
        $email = $this->faker->unique()->email;
        $payload = [
            'password' => $password = 'Passwor3',
            'new_password' => $newPassword = 'StrongPasswor3',
            'new_password_confirmation' => $newPassword,
            'email' => $newEmail = $this->faker->unique()->email
        ];

        $user = factory(User::class)->create([
            'password' => $password,
            'email' => $email,
        ]);

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), $payload);

        $user->refresh();

        Notification::assertSentTo($user, AccountChanges::class);
        Event::assertDispatched(PasswordUpdated::class);
        Event::assertDispatched(EmailUpdated::class);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'user',
            'message',
        ]);
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertEquals($newEmail, $user->email);
    }

    public function testCanUpdateEmailOnly()
    {
        Event::fake([
            PasswordUpdated::class,
            EmailUpdated::class
        ]);
        Notification::fake();
        $email = $this->faker->unique()->email;
        $payload = [
            'password' => $password = 'Passwor3',
            'email' => $newEmail = $this->faker->unique()->email
        ];

        $user = factory(User::class)->create([
            'password' => $password,
            'email' => $email,
        ]);

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), $payload);

        $user->refresh();

        Notification::assertSentTo($user, AccountChanges::class);
        Event::assertDispatched(EmailUpdated::class);
        Event::assertNotDispatched(PasswordUpdated::class);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'user',
            'message',
        ]);
        $this->assertEquals($newEmail, $user->email);
        $this->assertTrue(Hash::check($password, $user->password));
    }

    public function testCanUpdatePasswordOnly()
    {
        Notification::fake();
        Event::fake([
            PasswordUpdated::class,
            EmailUpdated::class
        ]);
        $email = $this->faker->unique()->email;

        $payload = [
            'password' => $password = 'Passwor3',
            'new_password' => $newPassword = 'SecurePassword3',
            'new_password_confirmation' => $newPassword,
        ];

        $user = factory(User::class)->create([
            'username' => 'johndoe',
            'password' => $password,
            'email' => $email,
        ]);

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), $payload);

        $user->refresh();

        Notification::assertSentTo($user, AccountChanges::class);
        Event::assertDispatched(PasswordUpdated::class);
        Event::assertNotDispatched(EmailUpdated::class);
        $response->assertSuccessful();
        $response->assertJsonStructure([
            'user',
            'message',
        ]);
        $this->assertTrue(Hash::check($newPassword, $user->password));
        $this->assertFalse(Hash::check($password, $user->password));
    }

    public function testShouldRequiredUserCurrentPassword()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'password' => 'wrongPassword',
            'email' => $user->email,
            'new_password' => null,
            'new_password_confirmation' => null,
        ]);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => 'These credentials do not match our records.'
        ]);
    }

    public function testShouldRequireUserValid2faOtpIfItIsEnabled()
    {
        $user = factory(User::class)->create([
            'google2fa_enabled' => 1
        ]);

        Passport::actingAs($user);

        $OTP = 123456;

        $this->mock(Google2FAService::class, function ($mock) use ($OTP, $user) {
            $mock->shouldReceive('checkOTP')
                 ->once()
                 ->with($user->google2fa_secret, $OTP)
                 ->andReturn(false);
        });

        $response = $this->putJson($this->updateRoute(), [
            'one_time_password' => $OTP,
            'email' => $user->email,
            'new_password' => null,
            'new_password_confirmation' => null,
        ]);

        $response->assertUnauthorized();
        $response->assertJson([
            'message' => trans('auth.2fa.invalid_otp')
        ]);
    }

    public function testShouldRequire2faOtpIfItIsEnabled()
    {
        $user = factory(User::class)->create([
            'google2fa_enabled' => 1
        ]);

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'email' => $user->email,
            'new_password' => null,
            'new_password_confirmation' => null,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'one_time_password' => 'The one time password field is required.'
        ]);
    }
    public function testShouldRequirePasswordIf2faItIsDisabled()
    {
        $user = factory(User::class)->create([
            'google2fa_enabled' => 0
        ]);

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'email' => $user->email,
            'new_password' => null,
            'new_password_confirmation' => null,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'password' => 'The password field is required.'
        ]);
    }

    public function testShouldRequireAnUniqueEmail()
    {
        Notification::fake();
        $otherUser = factory(User::class)->create();
        $user = factory(User::class)->create([
            'password' => $password = 'Password3'
        ]);
        $newPass = 'SecurePassword3';

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'username' => 'johndoe',
            'email' => $otherUser->email,
            'password' => $password,
            'new_password' => $newPass,
            'new_password_confirmation' => $newPass
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJson([
            'message' => 'The email has already been taken.'
        ]);
    }

    public function testNewPasswordShouldHaveAtLeastOneNumber()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);
        $newPass = 'SecurePassword';

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'password' => $password,
            'email' => $user->email,
            'new_password' => $newPass,
            'new_password_confirmation' => $newPass
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'new_password' => trans('validation.custom.password.number'),
        ]);
    }

    public function testNewPasswordShouldHaveAtLeastOneUppercase()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);
        $newPass = 'securepassword';

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'password' => $password,
            'email' => $user->email,
            'new_password' => $newPass,
            'new_password_confirmation' => $newPass
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'new_password' => trans('validation.custom.password.uppercase'),
        ]);
    }

    public function testNewPasswordShouldHaveAtLeastOneLowercase()
    {
        $user = factory(User::class)->create([
            'password' => $password = 'Passwor3'
        ]);
        $newPass = 'SECUREPASSWORD';

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'password' => $password,
            'email' => $user->email,
            'new_password' => $newPass,
            'new_password_confirmation' => $newPass
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'new_password' => trans('validation.custom.password.lowercase'),
        ]);
    }

    public function testNewPasswordShouldNotBeSimilarToUsername()
    {
        $user = factory(User::class)->create([
            'username' => 'johndoe3',
            'password' => $password = 'Passwor3'
        ]);
        $newPass = $user->username . 'Pass';

        Passport::actingAs($user);

        $response = $this->putJson($this->updateRoute(), [
            'password' => $password,
            'email' => $user->email,
            'new_password' => $newPass,
            'new_password_confirmation' => $newPass
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'new_password' => trans('validation.custom.password.matches_username'),
        ]);
    }
}
