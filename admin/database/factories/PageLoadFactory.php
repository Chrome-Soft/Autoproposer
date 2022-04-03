<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\PageLoad::class, function (Faker $faker) {
    return [
        'cookie_id'     => sha1($faker->password(20,20)),
        'from_url'      => $faker->url,
        'to_url'        => $faker->url,
        'partner_external_id' => function () {
            return factory(\App\Partner::class)->create()->external_id;
        },
        'created_at'    => function () use ($faker) {
            return Carbon::now()->subMonth(6)->addDays(rand(0, 6*30) - 6);
        }
    ];
});
