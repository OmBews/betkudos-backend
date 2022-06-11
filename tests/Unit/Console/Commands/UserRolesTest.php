<?php

namespace Tests\Unit\Console\Commands;

use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRolesTest extends TestCase
{
    use RefreshDatabase;

    public function testCanAssignRolesToUser()
    {
        Role::create(['name' => 'foo', 'guard_name' => 'api']);
        Role::create(['name' => 'bar', 'guard_name' => 'api']);

        $roles = ['foo', 'bar'];

        $user = factory(User::class)->create();

        $params = [
            'username' => $user->username,
            '--role' => $roles,
        ];

        $this->artisan('user:roles', $params)
             ->assertExitCode(0);

        $this->assertTrue($user->hasAnyRole($roles));
    }

    public function testCanRemoveUserRoles()
    {
        Role::create(['name' => 'foo', 'guard_name' => 'api']);
        Role::create(['name' => 'bar', 'guard_name' => 'api']);

        $roles = ['foo', 'bar'];

        $user = factory(User::class)->create();

        $user->assignRole($roles);

        $params = [
            'username' => $user->username,
            '--role' => $roles,
            '--remove' => true
        ];

        $this->artisan('user:roles', $params)
             ->assertExitCode(0);

        $this->assertTrue($user->hasAnyRole($roles));
    }
}
