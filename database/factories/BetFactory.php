<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Bets\Bet;
use Faker\Generator as Faker;

$factory->define(Bet::class, function (Faker $faker) {
    return [
        'user_id' => factory(\App\Models\Users\User::class),
        'code' => \Illuminate\Support\Str::random(10),
        'type' => Bet::TYPES[rand(0, count(Bet::TYPES) - 1)],
        'stake' => rand(1, 100),
        'profit' => rand(1, 100),
        'live' => $faker->boolean,
        'status' => Bet::STATUSES[rand(0, count(Bet::STATUSES) - 1)],
    ];
});
