<?php

namespace App\Console\Commands;

use App\Browser\Browser;
use Illuminate\Console\Command;

class CheckAddress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:address {address} {--s}';

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

            $protocol = $this->option('s') ? 'https://' : 'http://';
            $address =  $this->argument('address');

            $window->maximize()
                ->visit("{$protocol}{$address}")
                ->screenshot("address" . now()->format('H.i.s'));

            $this->line($window->getUrl());

            $window->quit();
        });
        $this->line('Done');
    }
}
