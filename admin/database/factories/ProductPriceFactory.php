<?php

use Faker\Generator as Faker;

$factory->define(App\ProductPrice::class, function (Faker $faker) {
    return [
        'product_id'    => function () {
            return factory(\App\Product::class)->create()->id;
        },
        'currency_id'    => function () {
            $currency = \App\Currency::inRandomOrder()->first();
            if ($currency) {
                return $currency->id;
            }

            return factory(\App\Currency::class)->create()->id;
        },
        'user_id'    => function () {
            return auth()->id() ?? factory(\App\User::class)->create()->id;
        },
        'price'     => rand(990,9900)
    ];
});
