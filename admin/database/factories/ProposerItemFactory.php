<?php

use Faker\Generator as Faker;

$factory->define(App\ProposerItem::class, function (Faker $faker) {
    return [
        'proposer_id'   => function () {
            return factory(\App\Proposer::class)->create()->id;
        },
        'user_id'   => function () {
            return factory(\App\User::class)->create()->id;
        },
        'type_id'      => function () {
            $types = \App\ProposerItemType::all();
            return $types->random()->id;
        },
        'html_content'  => $faker->randomHtml(),
        'product_id'  => function () {
            return factory(\App\Product::class)->create()->id;
        },
        'link'      => $faker->url
    ];
});
