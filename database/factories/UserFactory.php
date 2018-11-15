<?php

use Faker\Generator as Faker;

$factory->define(App\User::class, function (Faker $faker) {

    $tiers = ['free', 'paid', 'premium'];
    $location = \App\Location::all()->random()->name;

    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'phone_number' => $faker->phoneNumber,
        'contact_preference' => 'email',
        'email_verified_at' => now(),
        'location' => $location,
        'tier' => $tiers[rand(0,2)],
        'password' => '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', // secret
        'remember_token' => str_random(10),
    ];
});

$factory->defineAs(App\User::class, 'admin', function (Faker $faker) {

    return [
        'name' => 'admin',
        'email' => 'admin@email.com',
        'phone_number' => '07421353876',
        'contact_preference' => 'sms',
        'email_verified_at' => now(),
        'location' => 'Skipton',
        'tier' => 'premium',
        'password' => '$2y$10$GdD76ZCMlvV771qV67/XIuKcWzAMnI2/LemqvHYTlDWWO2RCmJVru', // admin
        'remember_token' => str_random(10),
    ];
});
