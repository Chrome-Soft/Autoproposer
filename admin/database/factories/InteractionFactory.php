<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Interaction::class, function (Faker $faker) {
    return [
        'type'      => function () { $xs = ['buy', 'view']; return $xs[rand(0,1)]; },
        'cookie_id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
        'user_id'   => \Ramsey\Uuid\Uuid::uuid4()->toString(),
    ];
});
