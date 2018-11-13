<?php

namespace App\Console\Commands;

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
    protected $signature = 'dvsa:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        $data['username'] = '';
        $data['password'] = '';

        $this->browser->browse(function ($browser) use ($data) {
        /**
         * @var $browser \Tpccdaniel\DuskSecure\Browser
        */

            $browser->visit('https://www.gov.uk/change-driving-test')
                    ->press('Start now')
                    ->type('username', $data['username'])
                    ->type('password', $data['password'])
                    ->press('booking-login')
                    ->click('#booking-login');

            $captcha = $browser->assertPresent('recaptcha_challenge_image');
            if ($captcha) {
                // Do something
            }

            $browser->click('#date-time-change')
                    ->click('test-choice-earliest');

            $slots = $browser->attribute('.SlotPicker-slot-label', 'data-datetime-label');

            $browser->screenshot('passed');
        });
    }
}
