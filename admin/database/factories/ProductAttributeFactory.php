<?php

use Faker\Generator as Faker;

$factory->define(App\ProductAttribute::class, function (Faker $faker) {
    return [
        'name'      => $faker->unique()->name,
        'slug'      => $faker->unique()->slug,
        'type_id'   => function () {
            return \App\ProductAttributeType::inRandomOrder()->first()->id;
        },
        'is_imported' => rand(0,1)
    ];
});
