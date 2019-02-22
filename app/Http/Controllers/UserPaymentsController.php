<?php

namespace App\Http\Controllers;

class UserPaymentsController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function addCard()
    {
        if ( is_null($this->user->stripe_id) ) {
            $this->user->createAsStripeCustomer();
        }

        $this->user->updateCard(request('token'));

        return response(201, 'success');
    }

    public function charge()
    {
        if ( ! $this->user->hasCardOnFile()) {
            return 'EH EH EH.';
        }
        
        $this->user->charge($amount);
    }
}
