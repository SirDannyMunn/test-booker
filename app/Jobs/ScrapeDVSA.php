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

    public $i;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($i)
    {
        $this->i = $i;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Redis::funnel('ScrapeDVSA')->limit(10)->then(function () {

            \Log::info($this->i);
            sleep(10);

        }, function () {

            \Log::info('Releasing');
            return $this->release(10);
        });
    }
}
