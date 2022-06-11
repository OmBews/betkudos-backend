<?php

namespace App\Services;

use App\Models\Users\User;
use App\Notifications\Auth\EmailVerification;

class UserService
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function create(array $attributes): User
    {
        $user = $this->user->newInstance();
        $user->fill($attributes);
        $user->save();

        return $user;
    }

    public function update(User $user, $attributes): bool
    {
        return $user->update($attributes);
    }
}
