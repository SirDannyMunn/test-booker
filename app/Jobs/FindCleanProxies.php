<?php

namespace App\Jobs;

use App\Browser\Browser;
use App\Modules\InteractsWithDVSA;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FindCleanProxies implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,
        InteractsWithDVSA;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Throwable
     */
    public function handle()
    {
        (new Browser)->browse(/**
         * @param $window
         * @param $proxy
         */
            function ($window, $proxy) {

                $this->proxy = $proxy;
                $this->window = $window;

                $this->login();
                $url = 'https://www.gov.uk/change-driving-test';
                $this->window->visit($url);
                $this->checkPage("Accessing site");
                $this->window->click('#get-started > a');
                $this->checkPage("Logging in");
            });
    }
}
