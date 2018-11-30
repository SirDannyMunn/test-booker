<?php

Route::get('/test', function () {

//    \App\Jobs\ScrapeDVSA::dispatch(\App\User::find(5), '232342');
//

    return \Illuminate\Support\Facades\Redis::connection('default')->dbsize();

    $users = \App\User::where('booked', false)
        ->whereDate('test_date', '>', now()->endOfDay()->addWeekdays(3))
        ->get();

    $frequency=1;
    $users->load(['locations' => function($location) use ($frequency) {
        return $location->where('last_checked', '<', now()->subMinutes($frequency)->timestamp);
    }]);

    $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();
    return $best_users = (new \App\User())->getBest($users, $locations);


//    return \Illuminate\Support\Facades\Queue::connection('database')->
//    if (env('APP_DEBUG')) {
//    }

//    return $users = \App\User::where('booked', false)
//    ->whereDate('test_date', '>', now()->endOfDay()->addWeekdays(3))
//    ->get();


    return $users = \App\User::where('booked', false)->whereDate('test_date', '>' , now()->addWeekdays(3))->with(['locations' => function($location) {
        return $location->where('last_checked', '<', now()->timestamp);
    }])->get();



//    $browser = new

//    return \Illuminate\Support\Facades\Redis::connection('default');
//    return ;
//     \Illuminate\Support\Facades\Artisan::call('dvsa:access');

});

Route::get('/', function () {
    return view('welcome');
});

Route::get('user/accept_booking', 'SlotController@accept');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
