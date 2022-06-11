<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */
use App\Models\Users\User;
use App\Services\Google2FAService;
use Faker\Generator as Faker;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    $google2FAService = app()->make(Google2FAService::class);
    return [
        'username' => Str::random(6),
        'email' => $faker->unique()->safeEmail,
        'password' => '$2y$10$BngrVJ7NaZw8TxysdRG9w.KzSh8NeCA6QoaUOEBDF66Y4bGLzcJMi', // Passwor3
        'google2fa_secret' => $google2FAService->generateSecretKey(),
        'ip_address' => $faker->ipv4,
        'email_verified_at' => now(),
        'remember_token' => Str::random(10),
        'google2fa_enabled' => 0,
        'balance' => 1000
    ];
});
