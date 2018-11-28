<?php

namespace App\Console\Commands;

use App\Location;
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
        /**
         * @var $browser \Tpccdaniel\DuskSecure\Browser
        */

            $browser->deleteCookies();
//
//            $browser->visit('https://www.whoishostingthis.com/tools/user-agent//')->screenshot("ip ".now()->format('h.m.i'))->quit();
//            return;
            // Login
            $browser->visit('https://www.gov.uk/change-driving-test')
                    ->clickLink('Start now')
                    ->type('#driving-licence-number', $data['username'])
                    ->type('#application-reference-number', $data['password'])
                    ->click('#booking-login');

            $browser->pause(rand(0,1000));

            // Handle captcha
            $captcha = $browser->checkPresent('recaptcha_challenge_image');
            if ($captcha) {
                // Do something
                $browser->screenshot("CAPTCHA-".now());
                $browser->tinker();
            }

            $browser->click('#date-time-change');
            $browser->click('#test-choice-earliest');
            $browser->pause(rand(0,1000));
            $browser->click('#driving-licence-submit');
            $browser->pause(rand(0,1000));

//            $all_slots = json_decode(file_get_contents(base_path('data/all_slots.json')), true);
            foreach ($locations as $location) {
                // Breakage happening here
                $browser->pause(rand(500,1500));
                $browser->click('#change-test-centre');
                $browser->pause(rand(500,1500));
                $browser->type('#test-centres-input', $location->name);
                $browser->pause(rand(500,1500));
                $browser->click('#test-centres-submit');
                $browser->pause(rand(500,1500));
                $browser->clickLink(ucfirst($location->name));
                $slots = $this->scrapeSlots($browser);
//                $this->handleData($all_slots[$location->name], $location);
                $this->handleData($slots, $location);
            }

            $browser->quit();
        });
    }

    /**
     * @param $browser \Tpccdaniel\DuskSecure\Browser
     * @return array
     */
    public function scrapeSlots($browser)
    {
        $slots = [];
        foreach (array_slice($browser->elements('.SlotPicker-slot-label'), 10) as $element) {
            /** @var $element RemoteWebElement */
            $string = $element->findElement(
                WebDriverBy::className('SlotPicker-slot')
            )->getAttribute('data-datetime-label');

            $date = Carbon::parse(substr($string, 0, strrpos($string, ' ')))->toDateString();
            $time = substr($string, strrpos($string, ' '));

            if(!isset($slots[$date])) {
                $slots[$date] = ['date'=>$date,'times'=>[]];
            }

            array_push($slots[$date]['times'], $time);
        }
        return array_values($slots);
    }

    /**
     * @param $slots
     * @param $location
     * @return array
     */
    public function handleData($slots, $location)
    {
        $users = $location->users->sortBy('created_at')->sortByDesc('priority');
        $location->update(['last_checked' => now()->timestamp]);

        // Get users date where latest
        $latest_test_date = Carbon::parse($users->pluck('test_date')->sort()->last());
        // Remove unneeded dates
        $slots = $this->removeCrappySlots($slots, $latest_test_date);

        // Inputs: Slots, Users
        // Outputs: List of best suited users for slots based on:

        // With data, check all users with that area to see if match available

        $user_points = [];
        foreach ($slots as $slot) {
//            $slot = $slot['date'];
            $user_points[$slot] = [];
            foreach ($users as $user) {
                $id = $user->id;
                $user_points[$slot][$id] = 0;
                if (Carbon::parse($slot)->greaterThan($user->test_date))
                    continue;
                if($user->location == $location->name)
                    $user_points[$slot][$id]+=2;
                if($user->priority)
                    $user_points[$slot][$id]+=1;
            }
        }

        $eligible_candidates = array_slice($user_points, 0,
            count(array_where(array_first($user_points), function ($value) {
                return $value != 0;
            }))
        );

        $slots = array_map(function($names) {
            return array_filter($names);
        }, $eligible_candidates);

        foreach ($slots as $slot => $users) {
            $user_id = array_first(array_keys($slots[$slot]));
        }

        return $user_points;

        // When match, send notification
//        \Illuminate\Support\Facades\Auth::user()->notify(new \App\Notifications\ReservationMade(
//            Auth::user(),
//                // date
//            ));
        // Also make event for 15 minutes to give check if taken, if not give to next best candidate
    }

    /**
     * @param $slots
     * @param $latest_test_date Carbon
     * @return array
     */
    public function removeCrappySlots($slots, $latest_test_date)
    {
        // Loop though slots until get to the point then break and slice array with index
        foreach ($slots as $index => $slot) {
            if($latest_test_date->lessThanOrEqualTo($slot)) {
                return array_slice($slots,0, $index);
            }
        }
        return $slots;
    }
}