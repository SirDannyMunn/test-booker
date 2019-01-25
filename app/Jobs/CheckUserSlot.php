<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CheckUserSlot implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $user;
    private $userSlot;
    private $slot;

    /**
     * Create a new job instance.
     *
     * @param $user
     * @param $userSlot
     */
    public function __construct($user, $userSlot)
    {
        $this->user = $user;
        $this->userSlot = $userSlot;
        $this->slot = $userSlot->slot;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // if UserSlot taken
            // Return;
        if ($this->userSlot->slot->taken) {
            return;
        }

        $alternativeUserSlots = $this->slot->userSlots->sortByDesc('points')->sortBy('tries');

        // Load each user
        // Check each user availability (whether they currently have an offer open)
        // Disqualify any users who are being checked

        dispatch(new MakeReservation($alternativeUserSlots->first()->user, $this->userSlot));
        // Close old browser session
        // NEXT BEST USER
            // Hasn't just been offered slot.
            //

        // Dispatch ReservationManager()
    }
}
