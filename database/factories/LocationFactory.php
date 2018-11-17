<?php
/**
 * Created by PhpStorm.
 * User: danie
 * Date: 14/11/2018
 * Time: 17:59
 */

use Faker\Generator as Faker;

$factory->define(App\Location::class, function (Faker $faker) {

    return [
        'name' => $faker->city,
//        'last_checked' => 0
    ];
});
