<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Users\Devices\Device;
use Faker\Generator as Faker;
use Jenssegers\Agent\Agent;

$factory->define(Device::class, function (Faker $faker) {
    $agent = new Agent();
    $agent->setUserAgent($faker->userAgent);

    return [
        'name' => $agent->device(),
        'platform' => $agent->platform(),
        'browser' => $agent->browser(),
        'user_agent' => $agent->getUserAgent(),
        'type' => Device::DEVICE_TYPES[rand(0, 3)],
    ];
});
