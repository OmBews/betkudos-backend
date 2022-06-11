<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Teams\Team;
use Faker\Generator as Faker;

$factory->define(Team::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'bet365_id' => \Illuminate\Support\Str::random()
    ];
});
