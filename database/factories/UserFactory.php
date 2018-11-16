<?php

use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {

    $tiers = ['free', 'paid', 'premium'];
    $location = \App\Location::all()->random()->name;

    $testDate = now()->addMonths(0,2)->addWeeks(1,4)->addDays(1,7);

//    $preferedDate = $testDate->subMonths();

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone_number' => $faker->phoneNumber,
        'test_date' => $testDate,
//        'prefered_date' => $preferedDate,
        'contact_preference' => 'email',
        'email_verified_at' => now(),
        'location' => $location,
        'tier' => $tiers[rand(0,2)],
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});

$factory->defineAs(App\User::class, 'admin', function (Faker $faker) {

    $testDate = now()->addMonths(0,2)->addWeeks(1,4)->addDays(1,7);

    return [
        'name' => 'admin',
        'email' => 'admin@email.com',
        'phone_number' => '07421353876',
        'test_date' => $testDate,
        'contact_preference' => 'sms',
        'email_verified_at' => now(),
        'location' => 'Skipton',
        'tier' => 'premium',
        'password' => '$2y$10$GdD76ZCMlvV771qV67/XIuKcWzAMnI2/LemqvHYTlDWWO2RCmJVru', // admin
        'remember_token' => str_random(10),
    ];
});
