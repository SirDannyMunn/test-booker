<?php

Route::get('/test', function () {

//    Auth::user()->notify(new \App\Notifications\ReservationMade(Auth::user(), '12:00'));
//      return \Illuminate\Support\Facades\Artisan::call('dvsa:access');

//    Auth::user()->notify(new \App\Notifications\ReservationMade(
//        Auth::user(),
//        now()->toDateString()));

//    $users = \App\User::with(['locations' => function($location) {
//        return $location->where('last_checked', '<', now()->subMinutes(5)->timestamp);
//        return $location->where('last_checked', '<', now()->timestamp); // Remove in production
//    }])->get();

//    $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();
//
//    return $best_users = (new App\User)->getBest($users, $locations);
//
    \Illuminate\Support\Facades\Artisan::call('dvsa:access');

});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
