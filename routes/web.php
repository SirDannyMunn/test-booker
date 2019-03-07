    <?php

    use App\Notifications\ReservationMade;
    use App\Browser\Browser;
    use App\Jobs\ScrapeDVSA;
    use App\User;
    use App\Slot;

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('/test', function () {

        // return $slots = Slot::promotable()->get();
        return $slots = auth()->user()->notify(new ReservationMade(auth()->user(), Slot::find(1)));

        // $slots->random()->promotable();

        // return auth()->user()->defaultCard();

        return view('payment');

        return 'success';
    });
    
    Route::get('/home', 'HomeController@index')->name('home');

    Route::get('/payment', 'PaymentsController@index');
    
    Route::post('/payments/customer', 'UserPaymentsController@addCard');
    
    Route::get('/slot/{slot}/promote', 'SlotController@promote');

    Route::post('/slot/accept/sms', 'SlotController@accept');
    Route::get('/slot/accept', 'SlotController@accept');
    
    Auth::routes();