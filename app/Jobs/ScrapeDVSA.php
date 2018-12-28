<?php

namespace App\Jobs;

use App\Browser\Browser;
use App\Location;
use App\Notifications\ReservationMade;
use App\Modules\SlotManager;
use App\Proxy;
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

    public static $stage = 'Start';
    public $tries = 3;
    public $timeout = 240;
    private $user;
    private $toNotify;

    /** @var \Tpccdaniel\DuskSecure\Browser */
    private $window;

    /** @var Proxy */
    private $proxy;

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

    private function checkPage($stage)
    {
        static::$stage = $stage;
        $captcha = $this->window->captcha();
        $body = $this->window->html('body');

        if (!$captcha && $body != '') return;

        $this->proxy->failed();

        abort(500, $captcha ? 'Captcha found' : 'Blank Response');
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
        Redis::connection('default')->funnel($this->user->dl_number)->limit(1)->then(function() {

            (new Browser)->browse(function ($window, $proxy) {

                $this->window = $window;
                $this->proxy = $proxy;

                $this->deleteIncapsulaCookies();
                $this->login();

                $this->proxy->update(['last_used' => now()->toDateTimeString()]);

                $this->checkPage("At Dashboard");
                $this->goToCalendar();
                $this->checkPage("At Calendar");
                $this->loopLocations();

                if ($book=false) {
                    $this->makeBooking();
                }
                
                $this->window->quit();
            });

            }, function () {

                \Log::info('Releasing user');
                return $this->release(30);
            });
        }, function () {

            \Log::info('Releasing job');
            return $this->release(30);
        });

        $this->proxy->update(['completed' => $this->proxy->completed + 1, 'fails' => 0]);

        $this->sendNotifications($this->toNotify);
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

        $this->checkPage("Accessing site");

        $this->window->click('#get-started > a');

        $this->checkPage("Logging in");

        $this->window
            ->type('#driving-licence-number', decrypt($this->user->dl_number))
            ->type('#application-reference-number', decrypt($this->user->ref_number))
            ->click('#booking-login');

        $this->window->pause(rand(250, 1000));
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
        $users = User::all();

        foreach ($to_notify->collapse()->groupBy('user.id') as $item) { /* @var $user User*/ /* @var $item Collection
         *  collection of users and slots, ranked and sorted to acquire best match
         */
            $item = $item->sortByDesc('date')->sortByDesc('user.points')[0];
            $user = $users->find($item['user']['id']);
            $user->notify(new ReservationMade($user, $item));
        }
    }

    private function loopLocations()
    {
        $slotManager = new SlotManager;
        foreach ($this->user->locations as $location) { /* @var $location Location */
            $this->window->pause(rand(250,1000))
                ->click('#change-test-centre');
            $this->checkPage("#change-test-centre");

            $this->window->pause(rand(1000,2000))
                ->type('#test-centres-input', $location->name)
                ->pause(rand(1000,2000))
                ->click('#test-centres-submit')
                ->pause(rand(1000,2000));
            $this->checkPage("#test-centres-submit");

            $this->window->clickLink(ucfirst($location->name));
            $this->checkPage("Changed calendar location");

            $slots = $this->getSlots($location->name);
            $location->update(['last_checked' => now()->timestamp]);
            $this->toNotify->push($slotManager->getMatches($slots, $location));
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
