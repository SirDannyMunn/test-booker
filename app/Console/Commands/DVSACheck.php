<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Browser\Browser;

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
                $browser->visit('https://google.com');
                $browser->screenshot('test');
        });
        return 'test';
    }
}
