<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\NotAllowedIps\NotAllowedIp;
use Faker\Generator as Faker;

$factory->define(NotAllowedIp::class, function (Faker $faker) {
    return [
        'ip_address' => $faker->ipv4
    ];
});
