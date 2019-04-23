<?php

namespace App\Modules;

trait ManagesUserSlots
{
    /** @var \App\Proxy */
    private $proxy;

    /** @var \Tpccdaniel\DuskSecure\Browser */
    private $window;

    public function reservationMade()
    {
        $this->userSlot->tried();
        
        $this->user->reservationMade(env('CRAWLER_ON') ? $this->window->driver->getSessionID() : 'test1234');

        dispatch(new CheckUserSlot(
            $this->userSlot, $this->user)
        )->onQueue('high')->delay(now()->addMinutes(15));
    }
}