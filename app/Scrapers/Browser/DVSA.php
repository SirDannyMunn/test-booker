<?php

namespace App\Scrapers\Browser;

use App\Scrapers\DuskTestCase;
use Tpccdaniel\DuskSecure\Browser;

class DVSA extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     * @throws \Throwable
     */
    public function testCheckForChanges()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('https://google.com')
                    ->screenshot('test')
                    ->assertSee('Laravel');
        });
    }
}