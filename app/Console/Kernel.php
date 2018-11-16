<?php

namespace App\Console;

use App\Location;
use App\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
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
            $users = User::where('booked', false)->with(['locations' => function($location) {
                return $location->where('last_checked', '<', now()->subMinutes(5)->timestamp);
            }])->get();

            $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();

            // Get best users to use for scraping - ensuring to include all locations.
            $best_users = (new User)->getBest($users, $locations);
            // send to process with delay if necessary.
            

            // Add each scrape task to queue
        })->everyFiveMinutes();

//        Artisan::call('dvsa:access', ['--getslot'=>1, 'user'=>1]);
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
