<?php

use Faker\Generator as Faker;

$factory->define(App\ProposerType::class, function (Faker $faker) {
    return [
        'name'  => $faker->word,
        'key'   => $faker->slug
    ];
});
