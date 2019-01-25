<?php

namespace App\Jobs;

use App\Browser\Browser;
use App\Proxy;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;
use App\Modules\InteractsWithDVSA;

class ConfirmBooking implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 240;
    private $user;

    /** @var Proxy */
    private $proxy;

    /** @var \Tpccdaniel\DuskSecure\Browser */
    private $window;

    /**
     * Create a new job instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function handle()
    {
        Redis::connection('default')->funnel('DVSA')->limit(10)->then(function () {

            (new Browser)->browse(function ($window, $proxy) {

                $window->click("#confirm-changes");

                $window->checkPresent('// Validation');

                $proxy->update(['last_used' => now()]);
                
            }, true, false, $this->user->browser_session_id);
            
            // Send email

        }, function () {

            \Log::info('Releasing job');
            return $this->release(30);
        });
    }
}
