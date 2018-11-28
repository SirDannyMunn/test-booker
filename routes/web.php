<?php

Route::get('/test', function () {

     \Illuminate\Support\Facades\Artisan::call('dvsa:access');

});

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
