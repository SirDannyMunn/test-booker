<?php

Route::get('/test', function () {

     \Illuminate\Support\Facades\Artisan::call('dvsa:access');

});

Route::get('/', function () {
    return view('welcome');
});

Route::get('user/accept_booking', 'SlotController@accept');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
