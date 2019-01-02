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

class ScrapeDVSA implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,
        InteractsWithDVSA;

    public static $stage = 'Start';
    public $tries = 3;
    public $timeout = 240;
    private $user;
    private $toNotify;

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
        $this->toNotify = collect();
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
        Redis::connection('default')->funnel($this->user->dl_number)->limit(1)->then(function() {
            
            (new Browser)->browse(function ($window, $proxy) {

                $this->proxy = $proxy;
                $this->window = $window;        

                $this->deleteIncapsulaCookies();
                $this->login();

                cache([$proxy->proxy => $window], 15);
                
                $this->proxy->update(['last_used' => now()->toDateTimeString()]);

                $this->checkPage("At Dashboard");
                $this->goToCalendar();
                $this->checkPage("At Calendar");
                $this->loopLocations();

                if ($book=false) {
                    $this->makeBooking();
                }
                
                // $this->window->quit();
            });

            $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

        }, function () {

            \Log::info('Releasing user');
            return $this->release(30);
        });
        }, function () {

            \Log::info('Releasing job');
            return $this->release(30);
        });
        $this->sendNotifications($this->toNotify);
    }
}
