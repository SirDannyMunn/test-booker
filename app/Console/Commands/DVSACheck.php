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

        $browser->addCookie()

            $browser->visit('https://munn.pro')
                    ->click('wiener');

            // Login
//            $browser->visit('https://www.gov.uk/change-driving-test')
//                    ->clickLink('Start now')
//                    ->type('#driving-licence-number', $data['username'])
//                    ->type('#application-reference-number', $data['password'])
//                    ->click('#booking-login');
//
//            // Handle captcha
//            $captcha = $browser->checkPresent('recaptcha_challenge_image');
//            if ($captcha) {
//                // Do something
//                $browser->screenshot("CAPTCHA-".now());
//            }
//
//            $browser->click('#date-time-change');
//            if ($user->prefered_date == 'asap')
//                $browser->click('#test-choice-earliest');
//            $browser->click('#driving-licence-submit');
//
//            // Scrape locations
//            foreach ($locations as $location) {
//                $browser->click('#change-test-centre')
//                        ->type('#test-centres-input', $location->name)
//                        ->click('#test-centres-submit')
//                        ->clickLink(ucfirst($location->name));
//                $slots = $this->scrapeSlots($browser);
//                $this->handleData($slots, $location);
//            }
//
//            $browser->quit();
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
        // Outputs: Best users for best slots based on:
        //           prefered location
        //           priority
        //           created_at
        // With data, check all users with that area to see if match available
        $user_points = [];
        foreach ($users as $user) {
            $id = $user->id;
            $user_points[$id]=['slots'=>[],'user'=>$user];
            if($user->location == $location->name) {
                $user_points[$id]['location'] = 1;
            }
            foreach ($slots as $slot) {

            }
        }

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
        foreach ($slots as $index => $array) {
            if($latest_test_date->lessThanOrEqualTo($array['date'])) {
                return array_slice($slots,0, $index);
            }
        }
    }
}