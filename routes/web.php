    <?php

    use App\Browser\Browser;

    Route::get('/test', function () {

        $user = \App\User::find(1);
//        $user->notify(new \App\Notifications\ReservationMade($user, \App\Slot::find(41)));

        $userSlots = $user->userSlots->first()->slot->userSlots;

        return $userSlots->sortByDesc('points')->sortBy('tries')->pluck('points');
//        return dispatch_now(new \App\Jobs\ScrapeDVSA(\App\User::find(1)));

        return 'success';
    });

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('user/accept_booking', function() {
//        return 'test';
        $user = \App\User::where('action_code', request('user'))->get()->first();
        dispatch_now(new \App\Jobs\ConfirmBooking($user));

        // Do other stuff maybe.

        // If not action give to another user.
    });

    Auth::routes();

    Route::get('/home', 'HomeController@index')->name('home');
