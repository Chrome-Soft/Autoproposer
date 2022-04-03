<?php

use Faker\Generator as Faker;

$factory->define(App\Product::class, function (Faker $faker) {
    return [
        'name'          => $faker->unique()->words(2, true),
        'description'   => $faker->sentence,
        'slug'          => $faker->unique()->slug,
        'user_id'       => function () {
            return factory(App\User::class)->create()->id;
        },
        'link'          => $faker->url
    ];
});
