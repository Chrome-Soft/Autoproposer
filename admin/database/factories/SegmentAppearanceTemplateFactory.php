<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\SegmentAppearanceTemplate;
use Faker\Generator as Faker;

$factory->define(SegmentAppearanceTemplate::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'slug' => $faker->unique->slug,
        'css_template' => $faker->name
    ];
});
