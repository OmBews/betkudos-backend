<?php

namespace Tests\Feature\Http\Controllers\Bost;

use App\Models\NotAllowedIps\NotAllowedIp;
use App\Models\Sessions\Logs\SessionLog;
use App\Models\Sessions\Session;
use App\Models\Users\Devices\Device;
use App\Models\Users\User;
use App\Repositories\SessionLogRepository;
use App\Repositories\SessionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Laravel\Passport\Token;
use Tests\TestCase;

class SessionControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\RolesAndPermissionsSeeder::class);
    }

    protected function sessionsIndexRoute()
    {
        return route('sessions.index');
    }

    protected function sessionsSearchRoute($value, string $field = 'user_id', string $filter = SessionRepository::DEFAULT_FILTER)
    {
        return route('sessions.search', [
            'value' => $value,
            'field' => $field,
            'filter' => $filter
        ]);
    }

    protected function sessionsFilterRoute(string $filter = SessionRepository::DEFAULT_FILTER)
    {
        return route('sessions.filter', [
            'filter' => $filter
        ]);
    }

    protected function blockSessionRoute(Session $session)
    {
        return route('sessions.block-ip', [
           'session' => $session->id
        ]);
    }

    protected function createUsersAndSessions(int $howMany = Session::PER_PAGE)
    {
        $genSessions = function ($user) {
            $device = $user->devices()->save(
                factory(Device::class)->make()
            );

            $session = $user->sessions()->save(
                factory(Session::class)->make([
                    'device_id' => $device->id
                ])
            );

            $session->logs()->createMany(
                factory(SessionLog::class, 2)->make([
                    'user_id' => $user->id
                ])->toArray()
            );
        };

        return factory(User::class, $howMany)
                ->create()
                ->each($genSessions);
    }

    protected function createSessionsFromDate($date, int $userId, $amount = 10)
    {
        return factory(Session::class, $amount)->create([
            'user_id' => $userId,
            'device_id' => $this->faker->randomDigit,
            'created_at' => $date,
        ]);
    }

    // Get sessions

    public function testUserCanGetSessionHistory()
    {
        $this->createUsersAndSessions();

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsIndexRoute());

        $response->assertSuccessful();
        $response->assertJsonCount(Session::PER_PAGE, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function testUserCanNotGetSessionHistoryWithoutPermission()
    {
        Passport::actingAs(factory(User::class)->create());

        $response = $this->getJson($this->sessionsIndexRoute());

        $response->assertForbidden();
        $response->assertJsonMissing([
            'data',
            'links',
            'meta'
        ]);
    }

    // Search sessions

    public function testUserCanSearchSessionsByUsername()
    {
        $user = $this->createUsersAndSessions(1)->first();

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->username,
            'username'
        ));

        $response->assertSuccessful();
        $response->assertJsonCount(1, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function testUserCanNotSearchSessionsWithoutPermissions()
    {
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->getJson($this->sessionsSearchRoute(
            'Some username',
            'username'
        ));

        $response->assertForbidden();
        $response->assertJsonMissing([
            'data',
            'links',
            'meta'
        ]);
    }

    public function testShouldReturnNotFoundIfSearchDoesNotFoundSessionsByUsername()
    {
        $user = factory(User::class)->create();

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->username,
            'username'
        ));

        $response->assertNotFound();
    }


    public function testUserCanSearchSessionsByUsernameAndFilterByAllSessions()
    {
        $user = factory(User::class)->create();
        // Last week sessions
        $this->createSessionsFromDate(now()->subWeek(), $user->id);
        // Last month sessions
        $this->createSessionsFromDate(now()->subMonth(), $user->id);
        // Today's sessions
        $this->createSessionsFromDate(now(), $user->id);

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->username,
            'username',
            SessionRepository::ALL_SESSIONS_FILTER
        ));

        $response->assertSuccessful();
        $response->assertJsonCount(30, 'data');
        $response->assertJsonStructure([
            'data',
            'meta',
            'links'
        ]);
    }

    public function testShouldReturnNotFoundIfTheSearchedUsernameDoesNotExists()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            'Some username',
            'username'
        ));

        $response->assertNotFound();
        $response->assertJsonMissing([
            'data',
            'meta',
            'links'
        ]);
        $response->assertJson([
            'message' => 'Sessions not found'
        ]);
    }

    public function testUserCanSearchSessionsByUserId()
    {
        $user = factory(User::class)->create();

        $this->createSessionsFromDate(now(), $user->id, Session::PER_PAGE);

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute($user->id));

        $response->assertSuccessful();
        $response->assertJsonCount(Session::PER_PAGE, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function testUserCanSearchSessionsByUserIdAndFilterByTodaySessions()
    {
        $user = factory(User::class)->create();
        // Last week sessions
        $this->createSessionsFromDate(now()->subWeek(), $user->id);
        // Last month sessions
        $this->createSessionsFromDate(now()->subMonth(), $user->id);
        // Today's sessions
        $this->createSessionsFromDate(now(), $user->id);

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->id,
            'user_id',
            SessionRepository::TODAY_SESSIONS_FILTER
        ));

        $response->assertSuccessful();
        $response->assertJsonCount(10, 'data');
        $response->assertJsonStructure([
            'data',
            'meta',
            'links'
        ]);
    }

    public function testUserCanSearchSessionsByUserIdAndFilterByAllSessions()
    {
        $user = factory(User::class)->create();
        // Last week sessions
        $this->createSessionsFromDate(now()->subWeek(), $user->id);
        // Last month sessions
        $this->createSessionsFromDate(now()->subMonth(), $user->id);
        // Today's sessions
        $this->createSessionsFromDate(now(), $user->id);

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->id,
            'user_id',
            SessionRepository::ALL_SESSIONS_FILTER
        ));

        $response->assertSuccessful();
        $response->assertJsonCount(30, 'data');
        $response->assertJsonStructure([
            'data',
            'meta',
            'links'
        ]);
    }

    public function testShouldReturnNotFoundIfSearchDoesNotFoundSessionsByUserIdWithTodayFilter()
    {
        $user = factory(User::class)->create();

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->id,
            'user_id',
            SessionRepository::TODAY_SESSIONS_FILTER
        ));

        $response->assertNotFound();
    }

    public function testShouldReturnNotFoundIfSearchDoesNotFoundSessionsByUserIdWithAllSessionsFilter()
    {
        $user = factory(User::class)->create();

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->id,
            'user_id',
            SessionRepository::ALL_SESSIONS_FILTER
        ));

        $response->assertNotFound();
    }

    public function testUserCanSearchSessionsByIpAddress()
    {
        $user = factory(User::class)->create();
        factory(Session::class, Session::PER_PAGE + 1)->create([
            'user_id' => $user->id,
            'device_id' => $this->faker->randomDigit,
            'ip_address' => $user->ip_address
        ]);

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->ip_address,
            'IP'
        ));

        $response->assertSuccessful();
        $response->assertJsonCount(Session::PER_PAGE, 'data');
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
    }

    public function testUserCanSearchSessionsByIpAddressAndFilterByTodaySessions()
    {
        $user = factory(User::class)->create();
        // Old sessions
        factory(Session::class, 10)->create([
            'user_id' => $user->id,
            'ip_address' => $user->ip_address,
            'device_id' => $this->faker->randomDigit,
            'created_at' => now()->subWeek(),
        ]);
        // Today's sessions
        factory(Session::class, 20)->create([
            'user_id' => $user->id,
            'ip_address' => $user->ip_address,
            'device_id' => $this->faker->randomDigit,
            'created_at' => now(),
        ]);
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->ip_address,
            'IP',
            SessionRepository::TODAY_SESSIONS_FILTER
        ));

        $response->assertSuccessful();
        $response->assertJsonCount(20, 'data');
        $response->assertJsonStructure([
            'data',
            'meta',
            'links'
        ]);
    }

    public function testShouldReturnNotFoundIfSearchDoesNotFoundSessionsByIpAddress()
    {
        $user = factory(User::class)->create();

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $user->ip_address,
            'IP'
        ));

        $response->assertNotFound();
        $response->assertJsonMissing([
            'data',
            'meta',
            'links'
        ]);
    }

    public function testCanValidateProvidedSearchFilter()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $this->faker->ipv4,
            'IP',
            $this->faker->word
        ));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'filter' => 'The selected filter is invalid.'
        ]);
    }

    public function testCanValidateProvidedSearchField()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsSearchRoute(
            $this->faker->userName,
            $this->faker->word
        ));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'field' => 'The selected field is invalid.'
        ]);
    }

    // Filter

    public function testUserCanFilterSessionsFromToday()
    {
        $this->createUsersAndSessions();

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsFilterRoute(
            SessionRepository::TODAY_SESSIONS_FILTER
        ));

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
        $response->assertJsonCount(30,'data');
    }

    public function testUserCanFilterSessionsAndGetAllSessions()
    {
        $user = factory(User::class)->create();
        // From last week
        $this->createSessionsFromDate(now()->subWeek(), $user->id);
        // From last mont
        $this->createSessionsFromDate(now()->subMonth(), $user->id);
        // From today
        $this->createSessionsFromDate(now(), $user->id);

        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsFilterRoute(
            SessionRepository::ALL_SESSIONS_FILTER
        ));

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data',
            'links',
            'meta'
        ]);
        $response->assertJsonCount(30,'data');
    }

    public function testCanValidateProvidedFilter()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $response = $this->getJson($this->sessionsFilterRoute(
            $this->faker->word
        ));

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonMissing([
            'data',
            'links',
            'meta'
        ]);
        $response->assertJsonValidationErrors([
            'filter' => 'The selected filter is invalid.'
        ]);
    }

    // Block session IP

    public function testCanBlockSessionIp()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $session = $this->createSessionsFromDate(now(), $user->id)->first();

        $session->token()->save(
          factory(Token::class)->make()
        );

        $response = $this->putJson($this->blockSessionRoute($session), [
            'block' => true
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('not_allowed_ips', [
            'ip_address' => $session->ip_address
        ]);
    }

    public function testCanUnBlockSessionIp()
    {
        $bookie = factory(User::class)->create();
        $bookie->assignRole('bookie');

        Passport::actingAs($bookie);

        $user = factory(User::class)->create();

        $session = $this->createSessionsFromDate(now(), $user->id)->first();

        factory(NotAllowedIp::class)->create([
            'ip_address' => $session->ip_address
        ]);

        $this->assertDatabaseHas('not_allowed_ips', [
            'ip_address' => $session->ip_address
        ]);

        $response = $this->putJson($this->blockSessionRoute($session), [
            'block' => false
        ]);

        $this->assertDatabaseMissing('not_allowed_ips', [
            'ip_address' => $session->ip_address
        ]);

        $response->assertSuccessful();
    }
}
