<?php

use Faker\Generator as Faker;

$factory->define(App\Segment::class, function (Faker $faker) {
    return [
        'name'          => $faker->unique()->word,
        'description'   => $faker->sentence,
        'slug'          => $faker->unique()->slug,
        'user_id'       => function () {
            return factory(App\User::class)->create()->id;
        }
    ];
});
