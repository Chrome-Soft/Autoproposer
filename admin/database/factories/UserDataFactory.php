<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\UserData::class, function (Faker $faker) {
    $resolutionRand = rand(0,5);
    return [
        'created_at' => function () use ($faker) {
            return Carbon::now()->subMonth(6)->addDays(rand(0, 6*30) - 6);
        },
        'partner_external_id' => function () {
            return factory(\App\Partner::class)->create()->external_id;
        },
        'cookie_id' => sha1($faker->password(20,20)),
        'device_manufacturer' => $faker->company,
        'device_product' => $faker->word,
        'device_is_mobile' => rand(0,1),
        'device_memory' => function () {
            $xs = [512,1,2,4,8,16,32];
            return $xs[rand(0,count($xs)-1)];
        },
        'device_screen_width' => function () use ($resolutionRand) {
            $xs = [768,1280,1920,2560,3840,7680];
            return $xs[$resolutionRand];
        },
        'device_screen_height' => function () use ($resolutionRand) {
            $xs = [500,720,1080,1440,2160,4320];
            return $xs[$resolutionRand];
        },
        'os_architecture' => function () { return rand(1,2)*32; },
        'os_name' => function () {
            $xs = ['OS X', 'Windows', 'Linux', 'Android', 'IOS'];
            return $xs[rand(0,count($xs)-1)];
        },
        'os_version' => $faker->buildingNumber,
        'browser_name' => function () {
            $xs = ['Chrome', 'Safari', 'Firefox', 'IE'];
            return $xs[rand(0,count($xs)-1)];
        },
        'browser_version' => $faker->buildingNumber,
        'browser_user_agent' => $faker->userAgent,
        'browser_language' => $faker->languageCode,
        'connection_bandwidth' => $faker->randomFloat(0, 1, 500),
        'connection_ip_address' => $faker->ipv4,
        'connection_effective_type' => function () { return rand(3,4) . 'g'; },
        'connection_real_type' => function () {
            $xs = ['mobile','wifi'];
            return $xs[rand(0,1)];
        },
        'timezone_offset' => function () { return rand(-5,5); },
        'email_domain' => $faker->freeEmailDomain,
        'phone_provider' => function () {
            $xs = [20,30,70];
            return $xs[rand(0,2)];
        },
        'location_country_code' => $faker->countryCode,
        'location_country_name' => $faker->country,
        'location_city_name' => $faker->city,
        'location_postal_code' => $faker->postcode,
        'location_latitude' => $faker->latitude,
        'location_longitude' => $faker->longitude
    ];
});
