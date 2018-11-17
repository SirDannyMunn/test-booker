<?php

namespace App\Http\Controllers;

use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\WebDriverBy;
use App\Browser\Browser;
use Illuminate\Console\Command;

class DVSAController extends Command
{
    /**
     * @var Browser
     */
    protected $browser;

    /**
     * Instantiate the class with browser.
     */
    public function __construct( )
    {
        parent::__construct();

        $this->browser = new Browser;
    }

    /**
     * @throws \Throwable
     */
    public function access()
    {
        $data['username'] = env('DL_NUMBER');
        $data['password'] = env('REF_NUMBER');

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

    /**
     * @param $browser
     */
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
