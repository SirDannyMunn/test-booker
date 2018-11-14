<?php
/**
 * Created by PhpStorm.
 * User: danie
 * Date: 14/11/2018
 * Time: 17:59
 */

use Faker\Generator as Faker;

$factory->define(App\UserLocation::class, function (Faker $faker) {

    $userId = \App\User::all()->pluck('id')->random();
    $locationId = \App\Location::all()->pluck('id')->random();

    return [
        'user_id' => $userId,
        'location_id' => $locationId
    ];
});
