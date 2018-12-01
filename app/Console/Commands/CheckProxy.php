<?php

namespace App\Console\Commands;

use App\Browser\Browser;
use Illuminate\Console\Command;

class CheckProxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:proxy';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Throwable
     */
    public function handle()
    {
        (new Browser)->browse(function($window) {
            /* @var $window \Tpccdaniel\DuskSecure\Browser */

            $window->visit('https://whatismyipaddress.com/proxy-check');

            $this->info($window->text('#ai > table'));

            $window->quit();
        });
    }
}
