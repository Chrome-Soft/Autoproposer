<?php

use Faker\Generator as Faker;

$factory->define(App\SegmentProduct::class, function (Faker $faker) {
    return [
        'segment_id'    => function () {
            return create('Segment')->id;
        },
        'product_id'    => function () {
            return factory(\App\Product::class)->create()->id;
        },
        'priority_id'    => function () {
            return factory(\App\SegmentProductPriority::class)->create()->id;
        },
        'user_id'    => function () {
            return factory(\App\User::class)->create()->id;
        },
    ];
});
