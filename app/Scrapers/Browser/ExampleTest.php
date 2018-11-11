<?php

namespace Dusk\Browser;

use App\Scrapers\DuskTestCase;
use Tpccdaniel\DuskSecure\Browser;

class ExampleTest extends DuskTestCase
{
    /**
     * A basic browser test example.
     *
     * @return void
     * @throws \Throwable
     */
    public function testBasicExample()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                    ->assertSee('Laravel');
        });
    }
}
