<?php /** @noinspection PhpInconsistentReturnPointsInspection */

namespace App\Jobs;

use App\Modules\ProxyManager;
use App\User;
//use App\Proxy;
//use Carbon\Carbon;
use App\Browser\Browser;
//use App\Jobs\MakeReservation;
use Illuminate\Bus\Queueable;
use App\Modules\InteractsWithDVSA;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Location;
use App\Modules\SlotManager;
use Carbon\Carbon;
use Tests\DummyData;

class GetNewUserSlots implements ShouldQueue
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
        $this->slotManager = new SlotManager;
    }

    public function handle()
    {
        Redis::connection('default')->funnel('DVSA')->limit(env("PROXY_LIMIT"))->then(function () {
        Redis::connection('default')->funnel($this->user->dl_number)->limit(1)->then(function() {

            $this->slots = collect(env('CRAWLER_ON')
                ? $this->scrapeWebsite()
                : (new DummyData)->getDummySlots()
            );

            $this->slots = $this->slots->map(function ($item) {
                $slots = $this->slotManager->manageSlots($item['slots'], $item['location']);

                if (filled($slots)) return $slots;
            });

            if (filled($this->slots)) {
                $this->makeReservationEvents($this->slots);
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
        $this->window->quit();
        $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);
    }

    /**
     * @param $slots Collection
     */
    public function makeReservationEvents($slots)
    {
        shell_exec('echo "Slots found" 1>&2');

        foreach ($slots->collapse() as $slot) { // TODO - This collapse will likely need removing

            $bestUserSlot = $slot->getBestUserSlot();

            if (is_null($bestUserSlot)) {
                return; // TODO - wtf - Should this not continue?
            }

            dispatch(new MakeReservation(
                $bestUserSlot->user, $bestUserSlot
            ))->onQueue('high');
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