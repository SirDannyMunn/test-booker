<?php

namespace App\Jobs;

use App\Modules\ProxyManager;
use App\User;
//use App\Proxy;
//use Carbon\Carbon;
use App\Browser\Browser;
//use App\Jobs\MakeReservation;
use Illuminate\Bus\Queueable;
use App\Modules\InteractsWithDVSA;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Location;
use App\Modules\SlotManager;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Tests\DummyData;

class ScrapeDVSA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,
        InteractsWithDVSA;

    public $tries = 3;
    public $timeout = 240;

    private $user;
    private $slots;
    private $slotManager;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->slots = collect();
        $this->slotManager = new SlotManager;
    }

    public function handle()
    {
        Redis::connection('default')->funnel('DVSA')->limit(env("PROXY_LIMIT"))->then(function () {
        Redis::connection('default')->funnel($this->user->dl_number)->limit(1)->then(function() {

            if (env('CRAWLER_ON')) {
                $this->scrapeWebsite();
            }
            
            $this->slots = $this->slotManager->mapLocationSlots(
                env('CRAWLER_ON') ? $this->slots : (new DummyData)->getDummySlots('Skipton')
            );

            Log::notice('Updating proxy completions');

            $this->window->quit();
            $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

            if ($this->slots) {
                $this->makeReservationEvents();
            }
        }, function () {
//            return $this->release(30);
        });
        }, function () {
//            return $this->release(30);
        });
    }

    public function scrapeWebsite()
    {
        (new Browser)->browse(function ($window, $proxy) {
            $this->proxy = $proxy;
            $this->window = $window;
            $this->getToCalendar();
            $this->slots = $this->scrapeLocations($this->user->locations);               
        });
    }

    public function makeReservationEvents()
    {
        foreach ($this->slots as $slot) { /* @var $item Illuminate\Support\Collection */

            Log::notice('Notifying Users');

            $best = $slot->getBestUser();

            if (is_null($best)) {
                return;
            }

            Log::notice($best);

            dispatch(new MakeReservation(
                $best->user->id,
                $best
            ))->onQueue('medium');
        }
    }

    /**
     * Handles crawling failure.
     */
    public function failed()
    {
//        $this->handle();
    }

    public function __destruct()
    {
        if ($this->window) {
            $this->window->quit();
        }
    }
}
