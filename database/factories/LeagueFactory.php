<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Leagues\League;
use Faker\Generator as Faker;

$factory->define(League::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'bet365_id' => $faker->unique()->randomNumber(),
        'sport_id' => $faker->randomNumber(),
        'popular' => $faker->boolean,
        'active' => true,
        'cc' => 'br',
    ];
});
