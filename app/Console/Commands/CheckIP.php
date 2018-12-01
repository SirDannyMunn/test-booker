<?php

namespace App\Console\Commands;

use App\Browser\Browser;
use Illuminate\Console\Command;

class CheckIP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:ip';

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

            $window->visit('https://www.iplocation.net/');

//            $window->screenshot("IP " . now()->format('H.i.s'));

            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(11) > div > table'));
            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(12) > div > table'));
            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(13) > div > table'));
            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(14) > div > table'));

            $window->quit();
        });
    }
}
