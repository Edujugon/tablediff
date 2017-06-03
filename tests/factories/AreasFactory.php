<?php

/**
 * Project: TableDiff.
 * User: Edujugon
 * Email: edujugon@gmail.com
 * Date: 8/5/17
 * Time: 17:51
 */

$factory->define(Edujugon\TableDiffTest\Models\MainArea::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'description' => $faker->text,
        'country' => $faker->country,
        'city' => $faker->city,
    ];
});

$factory->define(Edujugon\TableDiffTest\Models\SubArea::class, function (Faker\Generator $faker) {

    return [
        'name' => $faker->name,
        'description' => $faker->text,
        'country' => $faker->country,
        'city' => $faker->city,
        'population' => $faker->randomNumber(),
    ];
});