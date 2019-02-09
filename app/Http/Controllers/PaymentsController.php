<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function signup()
    {
        $this->user->createAsStripeCustomer();
    }

    public function charge()
    {
        

        if ( ! $this->user->hasCardOnFile()) {
            return 'EH EH EH.';
        }
        
        $this->user->charge($amount)
    }
}
