<?php

Route::get('/test', function () {

//    \Illuminate\Support\Facades\Artisan::call('dvsa:access');

    return now()->subMinutes(5)->timestamp;
    Auth::user()->notify(new \App\Notifications\ReservationMade(Auth::user()));

//    $users = \App\User::with('locations')->get();
//
//    $location_points = [];
//    foreach ($users as $user) {
//        foreach ($user->locations as $location)
//        isset($location_points[$user->name][$location->name])
//            ?$location_points[$user->name][$location->name]+=1
//            :$location_points[$user->name][$location->name]=0;
//    }
//
//    return $location_points;

//    $users = \App\User::with('locations')->get();
//
//    $locations = $users->pluck('locations')->filter();
//
//    $users->transform(function($user) {
//        return collect($user)->put('location_points', $user->locations->count());
//    });
//
//    return $users;


//    $locations = \App\Location::whereHas('users')->with(['users'])->get();
//
//    $users = $locations->pluck('users');
//
//    $locations = $locations->transform(function($location) {
//        $location->users->map(function($user) {
//            return $user->only('id');
//        });
//
//        return $location;
//    });
//
//    return $locations;
});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
