<?php

namespace App\Jobs;

use App\Browser\Browser;
use App\Notifications\ReservationMade;
use App\User;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;

class ScrapeDVSA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 240;
    private $user;
    private $random;
    private $window;

    /**
     * Create a new job instance.
     *
     * @param $user
     * @param $random
     */
    public function __construct($user, $random)
    {
        $this->user = $user;
        $this->random = $random;
//        $this->browser = ;
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function handle()
    {
        Redis::connection('default')->funnel('ScrapeDVSA')->limit(10)->then(function () {

            \Log::info($this->user ." - ". now()->toTimeString() ." - ". $this->random);

            (new Browser)->browse(function ($window) {

                $this->window = $window;

                $this->login();

                \Log::info('logged in');

                $this->checkCaptcha();

                $this->goToCalendar();

                \Log::info('at calendar');

//                $all_slots = json_decode(file_get_contents(base_path('data/all_slots.json')), true);
                $to_notify = collect();
                foreach ($this->user->locations as $location) {
                    $this->window->pause(rand(250,1000))
                        ->click('#change-test-centre')
                        ->pause(rand(250,1000))
                        ->type('#test-centres-input', $location->name)
                        ->pause(rand(250,1000))
                        ->click('#test-centres-submit')
                        ->pause(rand(250,1000))
                        ->clickLink(ucfirst($location->name));

                    $slots = $this->scrapeSlots($location->name);
//                    $slots = $all_slots[$location->name];
                    $location->update(['last_checked' => now()->timestamp]);
                    $to_notify->push($this->getScores($slots, $location));
                }

                if ($book=false) {
                    $this->makeBooking();
                }

                $this->sendNotifications($to_notify);

                $this->window->quit();
            });
        }, function () {

            \Log::info('Releasing');
            return $this->release(10);
        });
    }

    /**
     * @param $location
     * @return array
     */
    public function scrapeSlots($location)
    {
        $slots = [];
        $slots[$location] = [];
        foreach (array_slice($this->window->elements('.SlotPicker-slot-label'), 0, 20) as $element) { /** @var $element RemoteWebElement */
            $string = $element->findElement(
                WebDriverBy::className('SlotPicker-slot')
            )->getAttribute('data-datetime-label');

            $slot = Carbon::parse($string)->toDateTimeString();

            array_push($slots[$location], $slot);
        }

        return array_values($slots);
    }

    private function login()
    {
        $this->window->visit('https://www.gov.uk/change-driving-test')
            ->clickLink('Start now')
            ->type('#driving-licence-number', decrypt($this->user->dl_number))
            ->type('#application-reference-number', decrypt($this->user->ref_number))
            ->click('#booking-login');

        $this->window->pause(rand(250, 1000));
    }

    private function checkCaptcha()
    {
        // Handle captcha
        $captcha = $this->window->checkPresent('recaptcha_challenge_image');
        if ($captcha) {
            // Do something
            $this->window->screenshot("CAPTCHA-".now()->format('h.m.i'));
            $this->line('FAILED - CAPTCHA FOUND');
        }
    }

    private function goToCalendar()
    {
        $this->window->click('#date-time-change')
            ->click('#test-choice-earliest')
            ->pause(rand(250, 1000))
            ->click('#driving-licence-submit')
            ->pause(rand(250, 1000));
    }

    /**
     * @param $to_notify Collection
     */
    private function sendNotifications($to_notify)
    {
        $list = $to_notify->collapse()->groupBy('user.id');

        $users = User::all();

        foreach ($list as $item) { /* @var $item Collection
         *  collection of users and slots, ranked and sorted to acquire best match
         */
            $item = $item->sortByDesc('date')->sortByDesc('user.points')[0];
            $user = $users->find($item['user']['id']);
            $user->notify(new ReservationMade($user, $item));
        }
    }

    /**
     * @param $slots
     * @param $location
     * @return \Illuminate\Support\Collection
     */
    private function getScores($slots, $location)
    {
        $users = $location->users->sortByDesc('priority');
        $slots = $this->removeSlotsAfter($slots,
            Carbon::parse($users->pluck('test_date')->sort()->last())
        );

        $user_points = [];
        foreach ($slots[0] as $slot) {
            $user_points[$slot] = [];
            foreach ($users as $user) {
                $id = $user->id;
                $user_points[$slot][$id] = 0;
                if (Carbon::parse($slot)->greaterThan($user->test_date))
                    continue;
                if ($user->location == $location->name)
                    $user_points[$slot][$id] += 2;
                if ($user->priority)
                    $user_points[$slot][$id] += 1;
            }
        }

        if (!$user_points) {
            $this->window->quit();
            return collect();
        }

        $eligible_candidates = $this->sliceEligibleCandidates($user_points);

        $slots = $eligible_candidates->map(function ($ids, $date) use ($eligible_candidates, $location) {
            $users = collect($ids)->filter()->sort()->map(function ($value, $key) {
                return ['id' => $key, 'points' => $value];
            })->values();

            $item = ['date' => $date,
                'location' => $location->name,
                'user' => $users[$eligible_candidates->keys()->search($date)]];

            return $item;
        })->values();

        return $slots;
    }

    /**
     * @param $user_points
     * @return \Illuminate\Support\Collection
     */
    private function sliceEligibleCandidates($user_points)
    {
        $eligible_candidates = array_where(array_first($user_points), function ($value) {
            return $value != 0;
        });

        return collect(array_slice($user_points, 0, count($eligible_candidates)));
    }

    /**
     * @param $slots
     * @param $latest_test_date Carbon
     * @return array
     */
    private function removeSlotsAfter($slots, $latest_test_date)
    {
        // Loop though slots until get to the point then break and slice array with index
        foreach ($slots as $index => $slot) {
            if ($latest_test_date->lessThanOrEqualTo($slot)) {
                return array_slice($slots, 0, $index);
            }
        }
        return $slots;
    }
}
