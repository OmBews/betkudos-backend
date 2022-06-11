<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Models\Sessions\Logs\SessionLog;

$factory->define(SessionLog::class, function (Faker $faker) {
    return [
        'ip_address' => $faker->ipv4,
        'action' => SessionLog::LOGIN_ACTIONS[rand(0, 1)],
    ];
});
