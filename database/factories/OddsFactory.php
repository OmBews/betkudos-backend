<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Markets\MarketOdd;
use Faker\Generator as Faker;

$factory->define(MarketOdd::class, function (Faker $faker) {
    return [
        'name' => ['Home', 'Draw', 'Away'][rand(0,2)],
        'odds' => $faker->randomFloat(2, 1.20, 100),
        'bet365_id' => \Illuminate\Support\Str::random(10),
        'match_market_id' => $faker->randomNumber(3)
    ];
});
