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
    private $slot;

    /**
     * Create a new job instance.
     *
     * @param $user
     * @param $slot
     */
    public function __construct($user, $slot)
    {
        $this->user = $user;
        $this->slot = $slot;
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


        // Close old browser session
        // NEXT BEST USER
            // Hasn't just been offered slot.
            //

        // Dispatch ReservationManager()
    }
}
