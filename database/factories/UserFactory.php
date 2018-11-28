<?php

use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {

    $tier = ['free', 'paid', 'premium'][rand(0,2)];
    $location = \App\Location::all()->random()->name;

    $testDate = now()->addMonths(rand(1,2))->addWeeks(rand(1,4))->addDays(rand(1,7));

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone_number' => $faker->phoneNumber,
        'test_date' => $testDate,
        'preferred_date' => 'asap',
        'dl_number' => env('DL_NUMBER'),
        'ref_number' => env('REF_NUMBER'),
        'contact_preference' => 'email',
        'email_verified_at' => now(),
        'priority' => $tier=='premium',
        'location' => $location,
        'tier' => $tier,
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});

$factory->defineAs(App\User::class, 'admin', function (Faker $faker) {

    $testDate = now()->addMonths(rand(1,2))->addWeeks(rand(1,4))->addDays(rand(1,7));

    return [
        'name' => 'admin',
        'email' => 'admin@email.com',
        'phone_number' => '07421353876',
        'test_date' => $testDate,
        'preferred_date' => 'asap',
        'dl_number' => env('DL_NUMBER'),
        'ref_number' => env('REF_NUMBER'),
        'contact_preference' => 'sms',
        'email_verified_at' => now(),
        'priority' => true,
        'location' => 'Skipton',
        'tier' => 'premium',
        'password' => '$2y$10$GdD76ZCMlvV771qV67/XIuKcWzAMnI2/LemqvHYTlDWWO2RCmJVru', // admin
        'remember_token' => str_random(10),
    ];
});
