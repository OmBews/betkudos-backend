<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Promotions\Promotion;
use Faker\Generator as Faker;

$factory->define(Promotion::class, function (Faker $faker) {
    return [
        'name' => $faker->unique()->name,
        'image' => $faker->imageUrl(),
        'priority' => $faker->unique()->randomNumber()
    ];
});
