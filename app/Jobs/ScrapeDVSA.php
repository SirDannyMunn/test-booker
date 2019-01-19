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
use Illuminate\Support\Facades\Redis;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Location;
use App\Modules\SlotManager;

class ScrapeDVSA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,
        InteractsWithDVSA;
//    use InteractsWithDVSA;

    public $tries = 3;
    public $timeout = 240;

    private $user;
    private $toNotify;
    private $slotManager;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->toNotify = collect();
        $this->slotManager = new SlotManager;
    }

    public function handle()
    {
//        $slots = json_decode('[["2019-02-27 08:10:00","2019-03-04 08:10:00","2019-03-08 08:10:00","2019-03-11 08:10:00","2019-03-12 08:10:00","2019-03-12 14:32:00","2019-03-13 08:10:00","2019-03-13 09:07:00","2019-03-13 11:11:00","2019-03-13 12:38:00","2019-03-13 13:35:00","2019-03-13 14:32:00","2019-03-13 15:29:00","2019-03-14 08:10:00","2019-03-14 09:07:00","2019-03-14 10:14:00","2019-03-14 11:11:00","2019-03-14 12:38:00","2019-03-14 13:35:00","2019-03-14 14:32:00"]]', true);
//        $userSlots = $this->slotManager->getMatches($slots, Location::where('name', 'Skipton')->get()->first());
//        $eligibleUsers = User::whereIn('id', $userSlots->pluck('user.id'))->get();
//        foreach ($userSlots->groupBy('user.id') as $item) { /* @var $item Illuminate\Support\Collection */
//
//            // Gets earliest (highest ranking) slot from each users' list
//            $best = collect($item)->sortByDesc('date')->sortByDesc('user.points')[0];
//
//            dispatch_now(new MakeReservation(
//                $eligibleUsers->find($best['user']['id']),
//                $best['userSlot']
//            ));
//        }

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

                $this->toNotify = $this->scrapeUserLocations($this->user->locations)->map(function ($item) {
                    return $slots = $this->slotManager->getMatches($item['slots'], $item['location']);
                    // TODO - run reservation process straight after match made?
                        // Review implications
                            // -
                    // $slots;
                });
            });

            // Test
//            $slots = collect(
//                json_decode('{"slots":[["2019-02-27 08:10:00","2019-03-07 14:32:00","2019-03-11 08:10:00","2019-03-11 09:07:00","2019-03-11 12:38:00","2019-03-11 13:35:00","2019-03-11 14:32:00","2019-03-12 08:10:00","2019-03-12 11:11:00","2019-03-12 12:38:00","2019-03-12 13:35:00","2019-03-12 14:32:00","2019-03-13 08:10:00","2019-03-13 12:38:00","2019-03-13 13:35:00","2019-03-13 14:32:00","2019-03-13 15:29:00","2019-03-14 08:10:00","2019-03-14 09:07:00","2019-03-14 11:11:00"]],"location":{"id":1,"name":"Skipton","last_checked":1547581520,"times_checked":0,"created_at":"2019-01-11 14:31:16","updated_at":"2019-01-15 19:45:20","pivot":{"user_id":1,"location_id":1}}}', true)
//            );
//            $this->toNotify = $this->slotManager->getMatches($slots['slots'], Location::find(1));
//            $userSlots = collect(json_decode('{"6":[{"user":{"id":6,"points":5},"date":"2019-02-27 08:10:00","location":"Skipton","userSlot":{"user_id":6,"slot_id":13,"points":5}},{"user":{"id":6,"points":2},"date":"2019-02-26 08:10:00","location":"Preston","userSlot":{"user_id":6,"slot_id":17,"points":2}},{"user":{"id":6,"points":2},"date":"2019-03-13 08:10:00","location":"halifax","userSlot":{"user_id":6,"slot_id":19,"points":2}}],"1":[{"user":{"id":1,"points":5},"date":"2019-03-08 08:10:00","location":"Skipton","userSlot":{"user_id":1,"slot_id":3,"points":5}},{"user":{"id":1,"points":2},"date":"2019-02-26 09:07:00","location":"Preston","userSlot":{"user_id":1,"slot_id":18,"points":2}}],"4":[{"user":{"id":4,"points":3},"date":"2019-03-11 08:10:00","location":"Skipton","userSlot":{"user_id":4,"slot_id":4,"points":3}}],"2":[{"user":{"id":2,"points":3},"date":"2019-02-25 08:10:00","location":"Preston","userSlot":{"user_id":2,"slot_id":15,"points":3}}],"7":[{"user":{"id":7,"points":2},"date":"2019-02-25 12:38:00","location":"Preston","userSlot":{"user_id":7,"slot_id":16,"points":2}}]}', true));

            $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

            $userSlots = $this->toNotify->groupBy('user.id');

            $eligibleUsers = User::whereIn('id', $userSlots->collapse()->pluck('user.id'))->get();
            foreach ($userSlots as $item) { /* @var $item Illuminate\Support\Collection */

                // Gets earliest (highest ranking) slot from each users' list
                $best = collect($item)->sortByDesc('date')->sortByDesc('user.points')[0];

                dispatch(new MakeReservation(
                    $eligibleUsers->find($best['user']['id']),
                    $best['userSlot']
                ))->onQueue('medium');
            }
        }, function () {

            \Log::info('Releasing user');
//            return $this->release(30);
        });
        }, function () {

            \Log::info('Releasing job');
//            return $this->release(30);
        });
    }

    public function makeReservationEvents()
    {
        $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

        $userSlots = $this->toNotify->groupBy('user.id');

        $eligibleUsers = User::whereIn('id', $userSlots->collapse()->pluck('user.id'))->get();
        foreach ($userSlots as $item) { /* @var $item Illuminate\Support\Collection */

            // Gets earliest (highest ranking) slot from each users' list
            $best = collect($item)->sortByDesc('date')->sortByDesc('user.points')[0];

            dispatch(new MakeReservation(
                $eligibleUsers->find($best['user']['id']),
                $best['userSlot']
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
