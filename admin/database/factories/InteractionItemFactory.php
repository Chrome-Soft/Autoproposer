<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\InteractionItem::class, function (Faker $faker) {
    return [
        'item_name' => $faker->word,
        'item_id'   => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'item_type' => function () {
            $x = rand(1,2);
            return $x == 1 ? \App\Product::class : \App\ProposerItem::class;
        }
    ];
});
