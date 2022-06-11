<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Bets\Selections\BetSelection;

$factory->define(BetSelection::class, function (Faker $faker) {
    return [
        'bet_id' => factory(\App\Models\Bets\Bet::class),
        'match_id' => factory(\App\Models\Events\Event::class),
        'odds' => $faker->randomFloat(2, 1.20, 100),
        'status' => BetSelection::STATUSES[rand(0, count(BetSelection::STATUSES) - 1)],
    ];
});
