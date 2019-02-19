<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    private $user;

    public function __construct()
    {
        $this->user = auth()->user();
    }

    public function index()
    {
        $plan = config( 'settings.plans.' . request('plan') );

        $intent = $this->getPaymentIntent($plan['price']);

        return view('payment', ['intent' => $intent]);
    }

    public function getPaymentIntent($price)
    {
        Stripe::setApiKey(env("STRIPE_SECRET"));

        return PaymentIntent::create([
            "amount" => $price,
            "currency" => "gbp",
            "allowed_source_types" => ["card"],
        ]);
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
        
        $this->user->charge($amount);
    }
}
