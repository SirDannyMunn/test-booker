<?php

use App\Browser\Browser;

Route::get('/test', function () {

//    return \App\Slot::where('datetime', '<', today()->addDays(50)->toDateTimeString())->delete();

    return (new \App\Jobs\ScrapeDVSA(\App\User::find(1)))->handle();
//    return \App\User::find(1)->userSlots;

    return 'success';
});

Route::get('/', function () {
    return view('welcome');
});

Route::get('user/accept_booking', 'SlotController@accept', function() {
    $user = \App\User::where('action_code', request('user'));
    dispatch_now(new \App\Jobs\ConfirmBooking($user));

    // Do other stuff maybe.

    // If not action give to another user.
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
