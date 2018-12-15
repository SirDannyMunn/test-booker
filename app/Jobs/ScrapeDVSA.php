<?php

namespace App\Jobs;

use App\Browser\Browser;
use App\Location;
use App\Notifications\ReservationMade;
use App\Modules\SlotManager;
use App\User;
use Carbon\Carbon;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class ScrapeDVSA // implements ShouldQueue
{
//    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 240;
    private $user;
    private $random;

    /**
     * @var \Tpccdaniel\DuskSecure\Browser
     */
    private $window;

    /**
     * Create a new job instance.
     *
     * @param $user
     * @param $random
     */
    public function __construct($user)
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
        Redis::connection('default')->funnel('ScrapeDVSA')->limit(10)->then(function () {
        Redis::connection('default')->funnel($this->user->id)->limit(1)->then(function() {

            \Log::info($this->user->name ." - ". now()->toTimeString() ." - ". $this->random);

            (new Browser)->browse(function ($window) {

                $this->window = $window;

                $this->deleteIncapsulaCookies();
                $this->login();
                $this->checkCaptcha();
                $this->goToCalendar();

                // $all_slots = json_decode(file_get_contents(base_path('data/all_slots.json')), true);
                // $slots = $all_slots[$location->name];

                $to_notify = collect();
                $slotManager = new SlotManager;
                foreach ($this->user->locations as $location) { /* @var $location Location */
                    $this->window
                    ->pause(rand(250,1000))
                    ->click('#change-test-centre')
                    ->pause(rand(250,1000))
                    ->type('#test-centres-input', $location->name)
                    ->pause(rand(250,1000))
                    ->click('#test-centres-submit')
                    ->pause(rand(250,1000))
                    ->clickLink(ucfirst($location->name));

                    $slots = $this->getSlots($location->name);
                    $location->update(['last_checked' => now()->timestamp]);
                    $to_notify->push($slotManager->getMatches($slots, $location));
                }

                if ($book=false) {
                    $this->makeBooking();
                }

                $this->sendNotifications($to_notify);

                $this->window->quit();
            });

        }, function () {

            \Log::info('Releasing user');
            return $this->release(10);
        });
        }, function () {

            \Log::info('Releasing job');
            return $this->release(10);
        });
    }

    private function deleteIncapsulaCookies()
    {
        $incapsula_cookies = array_where(array_pluck( (array) $this->window->getCookies(), 'name'), function ($cookie) {
            return str_contains($cookie, 'incap');
        });

        foreach ($incapsula_cookies as $cookie) {
            $this->window->deleteCookie($cookie);
        }
    }

    private function login()
    {
        $url = 'https://www.gov.uk/change-driving-test';
        $this->window->visit($url);

//        if (rand(0,1))
//            $this->window->back()->visit($url);

        $this->window->click('#get-started > a');

        $this->checkCaptcha();

        $this->window->screenshot(__FUNCTION__);
        $this->window
            ->type('#driving-licence-number', decrypt($this->user->dl_number))
            ->type('#application-reference-number', decrypt($this->user->ref_number))
            ->click('#booking-login');

        $this->window->pause(rand(250, 1000));
    }

    private function checkCaptcha()
    {
        if ($this->window->captcha()) {
            $this->window->pause(10000)->screenshot("CAPTCHA-".now()->format('h.m.i'));
            Log::info($this->window->captcha());
            abort(500, 'Captcha found');
        }
    }

    private function goToCalendar()
    {
        $this->window->screenshot(__FUNCTION__);

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
        $users = User::all();

        foreach ($to_notify->collapse()->groupBy('user.id') as $item) { /* @var $user User*/ /* @var $item Collection
         *  collection of users and slots, ranked and sorted to acquire best match
         */
            $item = $item->sortByDesc('date')->sortByDesc('user.points')[0];
            $user = $users->find($item['user']['id']);
            $user->notify(new ReservationMade($user, $item));
        }
    }

    /**
     * @param $location
     * @return array
     */
    public function getSlots($location)
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
}
