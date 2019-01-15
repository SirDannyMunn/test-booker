<?php

namespace App\Jobs;

use App\Notifications\ReservationMade;
use App\User;
use App\Browser\Browser;
use App\UserSlot;
use Illuminate\Bus\Queueable;
use App\Modules\InteractsWithDVSA;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class MakeReservation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,
        InteractsWithDVSA;

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

            (new Browser)->browse(/**
             * @param $window
             * @param $proxy
             */
                function ($window, $proxy) {

                $this->proxy = $proxy;
                $this->window = $window;

                $this->getToCalendar();

                $this->makeReservation($this->slot);

                $this->user->notify(new ReservationMade($this->user, $this->userSlot));
                $this->userSlot->update(['tries'=>$this->userSlot->tries+1]);

                dispatch(new CheckUserSlot(
                    $this->userSlot, $this->user)
                )->onQueue('high')->delay(now()->addMinutes(15));

                $this->user->update(["browser_session_id" => $window->driver->getSessionID()]);
            },

                true);

            $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

        }, function () {
            \Log::info('Releasing job');
            return $this->release(rand(40,60));
        });
        }, function () {
            \Log::info('Releasing job');
            return $this->release(rand(40,60));
        });
    }

    /**
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function failed()
    {
        $this->handle();
        \Log::alert('MAKE RESERVATION FAILED');
    }
}
