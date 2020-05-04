<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Panorama;
use Faker\Generator as Faker;

$factory->define(App\Panorama::class, function (Faker $faker) {
    return [
        'nama' => $faker->unique->randomNumber,
        'deskripsi' => $faker->text,
        'latitude' => rand(-50, 50) / 1000 + -0.026330,
        'longitude' => rand(-50, 50) / 1000 + 109.342504,
    ];
});
