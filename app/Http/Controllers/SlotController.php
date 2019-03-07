<?php

namespace App\Http\Controllers;

use \Log;
use App\Slot;
use App\User;
use Illuminate\Http\Request;

class SlotController extends Controller
{

    public function acceptEmail(Request $request)
    {
        // send request to crawler to finish slot changing process.
        $user = \App\User::where('action_code', request('user'))->get()->first();

        $this->book($user);

        // send booking confirmation

        // return to message page with success message & new slot details
        // (or notice that they will receive notice from dvsa)
    }

    public function acceptSms()
    {
        if (strtolower(request('text'))!="ok") {
            return;
        }

        $user = User::where('phone_number', request('msisdn'))->get()->first();

        $this->book($user);

        // return booking confirmation
    }

    public function book($user)
    {
        dispatch_now(new \App\Jobs\ConfirmBooking($user));
    }

    public function promote(Request $request)
    {
        $slot = Slot::find(request('slot'));

        if (!$slot->promotable()) {
            return response('failed', 422);
        }
        
        $userSlot = $slot->currentUserSlot();

        $userSlot->promote();

        return response('success', 200);
    }
}
