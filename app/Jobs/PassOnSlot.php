<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class PassOnSlot implements ShouldQueue
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
        if ($this->slot->taken) {
            return;
        }

        $userSlot = $this->slot->userSlots->where('tries', 0)->sortByDesc('points')->first();

        // MakeReservation($userSlot->user, $userSlot->slot)
    }
}
