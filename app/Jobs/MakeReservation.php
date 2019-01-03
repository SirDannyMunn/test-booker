<?php

namespace App\Jobs;

use App\User;
use App\Proxy;
use Carbon\Carbon;
use App\Browser\Browser;
use Illuminate\Bus\Queueable;
use App\Jobs\SendNotifications;
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

    /** @var \App\Proxy */
    private $proxy;

    /** @var \Tpccdaniel\DuskSecure\Browser */
    private $window;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user, $slot)
    {
        $this->user = $user;
        $this->slot = $slot;
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

            (new Browser)->browse(function ($window, $proxy) {
            
                $this->proxy = $proxy;
                $this->window = $window;
            
                $this->getToCalendar();
            
                $this->makeReservation();
            
                dispatch(new SendNotifications($this->toNotify));
            
                $this->user->update(["browser_session_id" => $window->driver->getSessionID()]);
            });

            $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

        }, function () {

            \Log::info('Releasing job');
            return $this->release(30);
        });
    }
}
