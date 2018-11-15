<?php

namespace App\Console\Commands;

use Facebook\WebDriver\Remote\HttpCommandExecutor;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use Illuminate\Console\Command;
use App\Browser\Browser;
use Illuminate\Support\Facades\Log;

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
    protected $signature = 'dvsa:access {--getslot} {--book}';

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
        $data['username'] = env('TEST_DL_NUMBER');
        $data['password'] = env('TEST_REF_NUMBER');

        $this->browser->browse(function ($browser) use ($data) {
        /**
         * @var $browser \Tpccdaniel\DuskSecure\Browser
        */

            $browser->visit('https://www.gov.uk/change-driving-test')
                    ->clickLink('Start now')
                    ->type('#driving-licence-number', $data['username'])
                    ->type('#application-reference-number', $data['password'])
                    ->click('#booking-login');

            $captcha = $browser->checkPresent('recaptcha_challenge_image');
            if ($captcha) {
                // Do something
                $browser->screenshot("CAPTCHA-".now());
            }

            $browser->click('#date-time-change')
                    ->click('#test-choice-earliest')
                    ->click('#driving-licence-submit');

            $this->scrapeSlots($browser);

            $browser->quit();
        });
    }

    public function scrapeSlots($browser)
    {
        $slots = [];
        foreach (array_slice($browser->elements('.SlotPicker-slot-label'), 10) as $element) {
            /** @var $element RemoteWebElement */
            $string = $element->findElement(WebDriverBy::className('SlotPicker-slot'))->getAttribute('data-datetime-label');

            $date = substr($string, 0, strrpos($string, ' '));
            $time = substr($string, strrpos($string, ' '));

            if(!isset($slots[$date])) {
                $slots[$date] = [];
            }

            array_push($slots[$date], $time);
        }
    }
}