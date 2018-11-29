<?php

namespace App\Console;

use App\Http\Controllers\DVSAController;
use App\Jobs\ScrapeDVSA;
use App\Location;
use App\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Artisan;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function() {
//            $users = User::where('booked', false)->with(['locations' => function($location) {
//                return $location->where('last_checked', '<', now()->subMinutes(5)->timestamp);
//            }])->get();
//
//            // Get best users to use for scraping - ensuring to include all locations.
//            $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();
//            $best_users = (new User)->getBest($users, $locations);

            // Split into groups of ten, send each to process, ensure only ten running at a time
            // Add each scrape task to queue
//            \Log::info('test');


            for ($i = 0; $i < 50; $i++) {
                ScrapeDVSA::dispatch($i);
            }

//                Artisan::call('dvsa:access');

        })->everyMinute();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
