<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\Laravel\Passport\Token::class, function (Faker $faker) {
    return [
        'id' => \Illuminate\Support\Str::random(100),
        'client_id' => $faker->randomDigit,
        'revoked' => 0,
        'expires_at' => now()->addDays(2),
    ];
});
