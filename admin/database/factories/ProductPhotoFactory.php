<?php

use Faker\Generator as Faker;

$factory->define(App\ProductPhoto::class, function (Faker $faker) {
    return [
        'product_id'    => function () {
            return factory('Product')->create()->id;
        },
        'user_id'       => function () {
            return auth()->id() ?? factory('User')->create()->id;
        },
        'image_path'    => $faker->url
    ];
});
