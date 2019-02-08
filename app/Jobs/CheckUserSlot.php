<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Nexmo\User\User;
use App\Browser\Browser;
use \Log;

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
        $this->user->update(['offer_open' => false]);

        if ($this->userSlot->slot->taken) {
            return;
        }

        $eligibleUser = $this->slot->getBestUser();

        if (!isset($eligibleUser) ) {
            
            // No one found :(            
            Log::notice("Slot hasn't been taken", ['slot'=>$this->slot, 'eligible_users'=>$eligibleUsers, 'alternativeUserSlots', $alternativeUserSlots]);
            return;
        }

        // Disqualify any users who have an offer open
        dispatch(new MakeReservation($eligibleUser, $this->userSlot));

        (new Browser(function($window) {
            $window->quit();
        }, null, null, $this->user->browser_session_id));
    }
}
