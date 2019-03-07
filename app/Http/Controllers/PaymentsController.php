<?php

namespace App\Http\Controllers;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Http\Request;

class PaymentsController extends Controller
{
    public function index()
    {
        if ( !request('plan') || request('plan')==auth()->user()->tier ) {
            // Do something
        }

        if ( !auth()->user()->canChangePlan()) {
            return "You've already used it now pal.";
        }

        $plan = config( 'settings.plans.' . request('plan') );

        $intent = $this->getPaymentIntent($plan['price'] * 100);

        return view('payment', ['intent' => $intent, 'plan' => $plan]);
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
}
