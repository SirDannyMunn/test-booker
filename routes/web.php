    <?php

    use App\Browser\Browser;
    use App\Jobs\ScrapeDVSA;
    use App\User;

    Route::get('/test', function () {

        return config('settings.plans.free')['price'];

        return view('payment');

        // return dispatch_now(new ScrapeDVSA(User::find(1)));
        // $user = \App\User::find(1);

        // $user->notify(new \App\Notifications\ReservationMade($user, $user->userSlots->first()->slot));

        // return $user;

//        return dispatch_now(new \App\Jobs\ScrapeDVSA(\App\User::find(1)));

        return 'success';
    });

    Route::get('/', function () {
        return view('welcome');
    });
    
    Route::get('/payment', 'PaymentsController@index');

    Route::get('user/accept_booking', function() {
//        return 'test';
        $user = \App\User::where('action_code', request('user'))->get()->first();
        dispatch_now(new \App\Jobs\ConfirmBooking($user));

        // Do other stuff maybe.

        // If not action give to another user.
    });

    Auth::routes();

    Route::get('/home', 'HomeController@index')->name('home');
