<?php

namespace App\Modules;

use App\Jobs\CheckUserSlot;
use App\Notifications\ReservationMade;

trait ManagesUserSlots
{
    /** @var \App\Proxy */
    private $proxy;

    /** @var \Tpccdaniel\DuskSecure\Browser */
    private $window;

    public function reservationMade()
    {
        $this->userSlot->tried();

        $this->user->notify(new ReservationMade($this->user, $this->slot));
        $this->user->update([
            "offer_open"=>true,
            "browser_session_id" => env('CRAWLER_ON') ? $this->window->driver->getSessionID() : "test{$this->user->id}"
        ]);

        dispatch(new CheckUserSlot(
            $this->userSlot, $this->user)
        )->onQueue('high')->delay(now()->addMinutes(15));
    }
}