<?php

use Faker\Generator as Faker;

$factory->define(App\Proposer::class, function (Faker $faker) {
    return [
        'name'              => $faker->word,
        'slug'              => $faker->unique()->slug,
        'description'       => $faker->sentences(2, true),
        'width'             => $faker->randomDigit,
        'height'            => $faker->randomDigit,
        'page_url'          => $faker->url,
        'max_item_number'   => rand(1,10),
        'partner_id'        => function () {
            return factory(\App\Partner::class)->create()->id;
        },
        'user_id'           => function () {
            return factory(\App\User::class)->create()->id;
        },
        'type_id'           => 2
    ];
});
