<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Sessions\Session;
use Faker\Generator as Faker;

$factory->define(Session::class, function (Faker $faker) {
    return [
        'oauth_access_token_id' => \Illuminate\Support\Str::random(100),
        'ip_address' => $faker->ipv4
    ];
});
