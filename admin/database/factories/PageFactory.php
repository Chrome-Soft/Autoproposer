<?php

use Faker\Generator as Faker;

$factory->define(App\Page::class, function (Faker $faker) {
    return [
        'name'  => $faker->unique()->word,
        'slug'  => $faker->unique()->word,
        'url'   => $faker->url,
        'partner_id'        => function () {
            return factory(\App\Partner::class)->create()->id;
        },
        'user_id'           => function () {
            return factory(\App\User::class)->create()->id;
        }
    ];
});
