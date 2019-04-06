<?php

namespace App\Modules;

trait ManagesUserSlots
{
    public function reservationMade()
    {
        $this->userSlot->tried();
        
        $this->user->reservationMade($this->window->driver->getSessionID());

        dispatch(new CheckUserSlot(
            $this->userSlot, $this->user)
        )->onQueue('high')->delay(now()->addMinutes(15));
    }
}