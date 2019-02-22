    <?php

    use App\Browser\Browser;
    use App\Jobs\ScrapeDVSA;
    use App\User;

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/test', function () {

        return auth()->user()->defaultCard();

        return view('payment');

        return 'success';
    });
    
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/payment', 'PaymentsController@index');
    
    Route::post('/payments/customer', 'UserPaymentsController@addCard');
    
    Route::get('user/accept_booking', function() {
        $user = \App\User::where('action_code', request('user'))->get()->first();
        dispatch_now(new \App\Jobs\ConfirmBooking($user));
    });
    
    Auth::routes();