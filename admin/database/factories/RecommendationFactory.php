<?php

use Faker\Generator as Faker;

$factory->define(\App\Recommendation::class, function (Faker $faker) {
    return [
        'segment_id'    => function () {
            return factory(\App\Segment::class)->create()->id;
        },
        'product_id'    => function () {
            return factory(\App\Product::class)->create()->id;
        },
        'order'         => 1
    ];
});
