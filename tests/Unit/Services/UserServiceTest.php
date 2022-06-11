<?php

namespace Tests\Unit\Services;

use App\Models\Users\User;
use App\Notifications\Auth\AccountChanges;
use App\Notifications\Auth\EmailVerification;
use App\Services\UserService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class UserServiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function createUserAttrs(): array
    {
        return [
            'username' => 'johndoe',
            'password' => 'secret',
            'email' => 'email@email.com',
            'ip_address' => '127.0.0.1'
        ];
    }

    protected function updateUserAttrs(): array
    {
        return [
            'email' => 'newemail@mail.com',
            'password' => 'secret'
        ];
    }

    public function testCanCreateAUser()
    {
        $attrs = $this->createUserAttrs();

        $service = app()->make(UserService::class);

        $user = $service->create($attrs);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($attrs['username'], $user->username);
        $this->assertEquals($attrs['email'], $user->email);
        $this->assertEquals($attrs['ip_address'], $user->ip_address);
        $this->assertTrue(Hash::check($attrs['password'], $user->password));
    }

    public function testShouldCallUserMethodsCorrectlyToCreateAnUser()
    {
        $attrs = $this->createUserAttrs();

        $this->mock(User::class, function ($mock) use ($attrs) {
            $mock->shouldReceive('newInstance')->once()->andReturn($mock);
            $mock->shouldReceive('fill')->once()->with($attrs);
            $mock->shouldReceive('save')->once();
        });

        $service = app()->make(UserService::class);

        $service->create($attrs);
    }

    public function testCanUpdateAnUser()
    {
        Notification::fake();

        $user = factory(User::class)->create([
            'email' => $email = $this->faker->email
        ]);

        $attrs = $this->updateUserAttrs();

        $service = app()->make(UserService::class);

        $updated = $service->update($user, $attrs);
        $user->refresh();

        $this->assertTrue($updated);
        $this->assertNotEquals($email, $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));
    }

    public function testShouldCallUserMethodsCorrectlyToUpdateAnUser()
    {
        Notification::fake();
        $user = \Mockery::mock(User::class);
        $attrs = $this->updateUserAttrs();

        $user->shouldReceive('update')->once()
             ->with($attrs)
             ->andReturn(true);

        $service = app()->make(UserService::class);

        $service->update($user, $attrs);
    }
}
