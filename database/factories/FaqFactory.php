<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\FAQs\Faq;
use Faker\Generator as Faker;

$factory->define(Faq::class, function (Faker $faker) {
    return [
        'question' => $faker->unique()->text(),
        'answer' => $faker->unique()->text(),
        'welcome' => $faker->boolean,
        'priority' => $faker->randomNumber(),
    ];
});
