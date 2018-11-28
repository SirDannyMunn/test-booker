<?php

namespace App\Console\Commands;

use App\Location;
use App\Notifications\ReservationMade;
use App\User;
use Carbon\Carbon;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Console\Command;
use App\Browser\Browser;

/**
 * Class DVSACheck
 * @package App\Console\Commands
 */
class DVSACheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dvsa:access {--getslot} {--book} {user?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks dvsa website for changes for earlier appointments';

    /**
     * @var \Tpccdaniel\DuskSecure\Browser
     */
    protected $browser;
    protected $users;

    /**
     * @var \Tpccdaniel\DuskSecure\Browser
     */
    protected $window;

    /**
     * Create a new command instance.
     *
     * @param Browser $browser
     */
    public function __construct(Browser $browser)
    {
        parent::__construct();

        $this->browser = $browser;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        $data['username'] = env('DL_NUMBER');
        $data['password'] = env('REF_NUMBER');

        $user = \Auth::user();
        $locations = Location::all();
        $this->users = User::all();

        $this->browser->browse(function ($browser) use ($data, $user, $locations) {

        $browser->visit('https://www.whoishostingthis.com/tools/user-agent//')->screenshot("ip ".now()->format('h.m.i'))->quit();

            $this->window = $browser;

            $this->window->deleteCookies();
//             Login
            $this->window->visit('https://www.gov.uk/change-driving-test')
                    ->clickLink('Start now')
                    ->type('#driving-licence-number', $data['username'])
                    ->type('#application-reference-number', $data['password'])
                    ->click('#booking-login');

            $this->window->pause(rand(0,1000));

//             Handle captcha
            $captcha = $this->window->checkPresent('recaptcha_challenge_image');
            if ($captcha) {
                // Do something
                $this->window->screenshot("CAPTCHA-".now());
                $this->line('FAILED - CAPTCHA FOUND');
            }

            $this->window->click('#date-time-change');
            $this->window->click('#test-choice-earliest');
            $this->window->pause(rand(0,1000));
            $this->window->click('#driving-licence-submit');
            $this->window->pause(rand(0,1000));

//            $all_slots = json_decode(file_get_contents(base_path('data/all_slots.json')), true);
            $to_notify = collect();
            foreach ($locations as $location) {
                // Breakage happening here
                $this->window->pause(rand(250,1000));
                $this->window->click('#change-test-centre');
                $this->window->pause(rand(250,1000));
                $this->window->type('#test-centres-input', $location->name);
                $this->window->pause(rand(250,1000));
                $this->window->click('#test-centres-submit');
                $this->window->pause(rand(250,1000));
                $this->window->clickLink(ucfirst($location->name));
                $slots = $this->scrapeSlots($location->name);
                $location->update(['last_checked' => now()->timestamp]);
//                $this->handleData($all_slots[$location->name], $location);
                $this->handleData($slots, $location);
                $to_notify->push($this->getScores($slots, $location));
            }

            $this->line('Sending emails...');

            $list = $to_notify->collapse()->groupBy('user.id');

            foreach ($list as $item) {
                $item = $item->sortByDesc('date')->sortByDesc('user.points')[0];
                $user = $this->users->find($item['user']['id']);
                $user->notify(new ReservationMade($user, $item));
            }

            $this->window->quit();
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
        foreach (array_slice($this->window->elements('.SlotPicker-slot-label'), 10) as $element) {
            /** @var $element RemoteWebElement */
            $string = $element->findElement(
                WebDriverBy::className('SlotPicker-slot')
            )->getAttribute('data-datetime-label');

            $date = Carbon::parse(substr($string, 0, strrpos($string, ' ')))->toDateString();

            array_push($slots[$location], $date);
        }
        return array_values($slots);
    }

    /**
     * @param $slots
     * @param $location
     * @return \Illuminate\Support\Collection
     */
    public function getScores($slots, $location)
    {
        $users = $location->users->sortByDesc('priority');
        $slots = $this->removeSlotsAfter($slots,
            Carbon::parse($users->pluck('test_date')->sort()->last())
        );

        $user_points = [];
        foreach ($slots as $slot) {
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
            return null;
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
    public function sliceEligibleCandidates($user_points)
    {
        $eligible_candidates = array_where(array_first($user_points), function ($value) {
            return $value != 0;
        });

        return collect(array_slice($user_points, 0, count($eligible_candidates)));
    }

    /**
     * @param $slots
     * @param $location
     * @return array
     */
    public function handleData($slots, $location)
    {

//        $slots = $eligible_candidates->map(function ($names, $date) {
//            return ['date' => $date, 'users' => collect($names)->filter()->sort()->map(function ($value, $key) {
//                return ['id' => $key, 'points' => $value];
//            })->values()];
//        })->values();

        // Send notifications
//        foreach ($slots as $key => $slot) {
//            $user = User::find($slot['users'][$key]['id']);
//            $user->notify(new ReservationMade($user, $slot['date']));
            // Also make event for 15 minutes to give check if taken, if not give to next best candidate
//        }

        return $slots;
    }

    /**
     * @param $slots
     * @param $latest_test_date Carbon
     * @return array
     */
    public function removeSlotsAfter($slots, $latest_test_date)
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