<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Redis;

class ScrapeDVSA implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 240;
    private $i;
    private $random;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($i, $random)
    {
        $this->i = $i;
        $this->random = $random;

//        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Illuminate\Contracts\Redis\LimiterTimeoutException
     */
    public function handle()
    {
//        \Log::info('handling');
        Redis::funnel('ScrapeDVSA')->limit(10)->then(function () {

            \Log::info($this->i ." - ". now()->toTimeString() ." - ". $this->random);

            sleep(5);

        }, function () {

            \Log::info('Releasing');
            return $this->release(10);
        });
    }
}
