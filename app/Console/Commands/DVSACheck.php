<?php

namespace App\Console\Commands;

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
        $this->browser->browse(function ($browser) {
        /**
         * @var $browser \Tpccdaniel\DuskSecure\Browser
        */

            $browser->visit('https://munn.pro')
                    ->pause(5000);

            try {
                $browser->assertSee('available');
            } catch (\Exception $e) {
                $browser->screenshot('failed');
            }

            $browser->screenshot('passed');
        });
        return 'test';
    }
}
