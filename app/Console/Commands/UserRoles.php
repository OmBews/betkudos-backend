<?php

namespace App\Console\Commands;

use App\Models\Users\User;
use Illuminate\Console\Command;

class UserRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:roles {username} {--role=*} {--r|remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add or remove roles to an user';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $user = $this->getUserByUsername($this->username());

        if (! $user) {
            $this->error("User with username $this->username not found");
        }

        if (! count($this->roles())) {
            $this->error("Expecting one least role to be assigned or removed");
        }

        $roles = implode(',', $this->roles());

        if ($this->remove()) {
            $this->warn("Removing roles: $roles");
            $this->removeRoles($user);
            return;
        }

        $this->warn("Singing roles: $roles");

        $user->assignRole($this->roles());

        $this->line("Roles: $roles. Signed to $user->username");
    }

    private function username()
    {
        return $this->argument('username');
    }

    protected function getUserByUsername(string $username): User
    {
        return User::where('username', $username)->first();
    }

    private function roles()
    {
        return $this->option('role');
    }

    private function remove()
    {
        return $this->option('remove');
    }

    private function removeRoles(User $user)
    {
        foreach ($this->roles() as $role) {
            $user->removeRole($role);
        }

        $this->line('Roles removed');
    }
}
