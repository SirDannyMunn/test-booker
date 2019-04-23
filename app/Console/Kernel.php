<?php

namespace App\Console;

use App\Jobs\FindCleanProxies;
use App\Jobs\ScrapeDVSA;
use App\Proxy;
use App\Slot;
use App\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Log;

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
        $schedule->command('horizon:snapshot')->everyMinute();

        $users = User::where('booked', false)->where('details_valid', true)
        ->whereDate('test_date', '>', now()->endOfDay()->addWeekdays(3))
        ->get();

        if (!filled($users)) {
            Log::notice('No Users - '.now()->toDateTimeString()); return;
        }

        // allowed visits per hour split between people and limited to >= 1
//        $frequency = round( 60 / (315 / count($users) ) - 0.499 ) ?: 2;
        $frequency = 1;

        $users->load(['locations' => function($location) use ($frequency) {
            return $location->where('last_checked', '<', now()->subMinutes($frequency)->timestamp);
        }]);

        $schedule->call(function() use ($users) {
            $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();
            
            $best_users = (new User)->getBest($users, $locations);
            // TODO - alternative (location oriented) solution
            // Get all locations (where not been checked within 5-10 minutes?)
            // Get 1 user account for every 2 locations
            // Shup event using random user

            foreach ($best_users as $user) {
                dispatch(new ScrapeDVSA($user))->onQueue('medium');
            }
        })->cron("*/{$frequency} * * * *")
            ->name('DVSA');
//            ->withoutOverlapping();
//          ->unlessBetween('23:00', '6:00');

        // TODO - Make another event which rapidly gets working proxies which can at least access site.
        $schedule->call(function() {
            if (Proxy::all()->count() < 50 && env('CRAWLER_ON')) {
                for ($i=0; $i < 6; $i++) {
                    dispatch(new FindCleanProxies)->onQueue('low')->delay(now()->addSeconds($i*10));
                }
            }
        })->everyMinute();

        $schedule->call(function() {
            $yesterday = today()->subDay();

            $oldSlots = Slot::where('datetime', '<', $yesterday->subMonth()->toDateTimeString());
            // TODO - Maybe store in spreadsheet or separate table before delete
            $oldSlots->userSlots->delete();
            $oldSlots->delete();

            $yesterdaysProxies = Proxy::whereBetween("created_at", [$yesterday->startOfDay(), $yesterday->endOfDay()])->get();
            // Email to me
        })->dailyAt("6:00");
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
