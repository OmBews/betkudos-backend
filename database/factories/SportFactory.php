<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Sports\Sport;
use Faker\Generator as Faker;

$factory->define(Sport::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'bet365_id' => $faker->randomNumber(2)
    ];
});
