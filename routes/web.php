    <?php

    use App\Browser\Browser;
    use App\Jobs\ScrapeDVSA;
    use App\User;

    Route::get('/test', function () {

        \Stripe\Stripe::setApiKey("sk_test_BZHmui7JytqwGDhmEYZZjVLs");

        return \Stripe\PaymentIntent::create([
            "amount" => 30,
            "currency" => "gbp",
            "allowed_source_types" => ["card"],
        ]);

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

    Route::get('user/accept_booking', function() {
//        return 'test';
        $user = \App\User::where('action_code', request('user'))->get()->first();
        dispatch_now(new \App\Jobs\ConfirmBooking($user));

        // Do other stuff maybe.

        // If not action give to another user.
    });

    Auth::routes();

    Route::get('/home', 'HomeController@index')->name('home');
