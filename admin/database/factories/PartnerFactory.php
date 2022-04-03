<?php

use Faker\Generator as Faker;

$factory->define(App\Partner::class, function (Faker $faker) {
    return [
        'external_id'           => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'name'                  => $faker->company,
        'slug'                  => $faker->unique()->slug,
        'url'                   => $faker->unique()->url,
        'is_anonymus_domain'    => !!rand(0,1),
        'user_id'               => function () {
            return factory(\App\User::class)->create()->id;
        }
    ];
});
