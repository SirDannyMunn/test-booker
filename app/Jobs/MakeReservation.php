<?php

namespace App\Jobs;

use App\Notifications\ReservationMade;
use App\User;
use App\Browser\Browser;
use App\UserSlot;
use Illuminate\Bus\Queueable;
use App\Modules\InteractsWithDVSA;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Modules\ManagesUserSlots;

class MakeReservation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,
        InteractsWithDVSA, ManagesUserSlots;

    public $tries = 3;
    public $timeout = 240;

    private $user;
    private $userSlot;
    private $slot;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @param $userSlot
     */
    public function __construct(User $user, UserSlot $userSlot)
    {
        $this->user = $user;
        $this->userSlot = $userSlot;
        $this->slot = $userSlot->slot;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function handle()
    {
        Redis::connection('default')->funnel('DVSA')->limit(env("PROXY_LIMIT"))->then(function () {
        Redis::connection('default')->funnel($this->user->dl_number)->limit(1)->then(function() {

            if (env('CRAWLER_ON')) {
                $this->makeReservation();
            }

            // Customer/slot management logic
            $this->reservationMade();

            $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

        }, function () {
            // return $this->release(rand(40,60));
        });
        }, function () {
            // return $this->release(rand(40,60));
        });
    }

    public function makeRservation()
    {
        // Browser automation logic
        (new Browser)->browse(function ($window, $proxy) {
            $this->proxy = $proxy;
            $this->window = $window;
            $this->getToCalendar();
            $this->reserveSlot($this->slot);
        }, true);
    }

    /**
     */
    public function failed()
    {
//        $this->handle();
        \Log::critical('MAKE RESERVATION FAILED', ['data' => $this]);
    }
}
