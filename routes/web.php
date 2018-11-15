<?php

Route::get('/test', function () {

//    \Illuminate\Support\Facades\Artisan::call('dvsa:access');

//    Auth::user()->notify(new \App\Notifications\ReservationMade(Auth::user()));

    $users = \App\User::with(['locations' => function($location) {
        return $location->where('last_checked', '<', now()->subMinutes(5)->timestamp);
    }])->get();

    $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();

    $best_users = collect();
    while (filled($locations)) {
        $location_points = [];
        foreach ($users as $key => $user) {
            $location_points[$user->name] = 0;
            foreach ($user->locations as $location) {
                // Check each user against remaining locations
                if ($locations->has($location->name)) {
                    // If user has one of locations, give point
                    $location_points[$user->name]+=1;
                }
            }
        }
        // Collect user with most points
        $sorted = array_keys(array_sort($location_points));
        $best_user = end($sorted);
        $best_users->push($best_user);
        // Remove user and all user's locations from lists
        $best_user = $users->where('name', $best_user)->first();
        $users->forget($users->search($best_user));
        $locations->forget($best_user->locations->pluck('name')->toArray());
    }

    return $best_users;
});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
