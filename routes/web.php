<?php

use App\Browser\Browser;

Route::get('/test', function () {
    
    // return phpinfo();
    (new \App\Jobs\ScrapeDVSA(\App\User::find(\App\User::all()->count())))->handle();
    return 'success';
    
    // $sessionID = null;
    // $sessionID = cache('session');
    // (new Browser)->browse(function ($window, $proxy) use ($sessionID) {
                
    //     $window->visit('https://google.com');
        
    //     cache(['session' => $window->driver->getSessionID()], 60);

    //     $window->screenshot($sessionID);
    // }, $sessionID);


//     \Illuminate\Support\Facades\Artisan::call('check:ip');

//    (new \App\Jobs\ScrapeDVSA(\App\User::find(1)))->handle();

//    \App\User::find(7)->notify(new \App\Notifications\ReservationMade(\App\User::find(7), ['date' => '12:01:12', 'location' => 'skippers']));

//    (new \App\Tasks\ProxyManager)->getNewProxy();

//     \Illuminate\Support\Facades\Artisan::call('dvsa:access');

//    $users = \App\User::where('booked', false)
//        ->whereDate('test_date', '>', now()->endOfDay()->addWeekdays(3))
//        ->get();
//
//    $frequency=1;
//    $users->load(['locations' => function($location) use ($frequency) {
//        return $location->where('last_checked', '<', now()->subMinutes($frequency)->timestamp);
//    }]);
//
//    $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();
//    return $best_users = (new \App\User())->getBest($users, $locations);


//    return \Illuminate\Support\Facades\Queue::connection('database')->
//    if (env('APP_DEBUG')) {
//    }

//    return $users = \App\User::where('booked', false)
//    ->whereDate('test_date', '>', now()->endOfDay()->addWeekdays(3))
//    ->get();

//    return $users = \App\User::where('booked', false)->whereDate('test_date', '>' , now()->addWeekdays(3))->with(['locations' => function($location) {
//        return $location->where('last_checked', '<', now()->timestamp);
//    }])->get();

//    $browser = new

//    return \Illuminate\Support\Facades\Redis::connection('default');
//    return ;

});

Route::get('/', function () {
    return view('welcome');
});

Route::get('user/accept_booking', 'SlotController@accept');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
