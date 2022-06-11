<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Events\Event;
use Faker\Generator as Faker;
use App\Models\Teams\Team;

$factory->define(Event::class, function (Faker $faker) {
    return [
        'bet365_id' => genModelUniqueID((new Event), 'bet365_id'),
        'bets_api_id' => rand(1000, 10000),
        'sport_id' => $faker->unique(true)->randomNumber(1),
        'home_team_id' => factory(\App\Models\Teams\Team::class),
        'away_team_id' => factory(\App\Models\Teams\Team::class),
        'league_id' => $faker->unique(true)->randomNumber(1),
        'starts_at' => time(),
        'time_status' => rand(0, 3),
        'last_bets_api_update' => $faker->unixTime,
    ];
});
