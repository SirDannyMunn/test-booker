<?php

namespace App\Console;

use App\Jobs\ScrapeDVSA;
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

        $users = User::where('booked', false)
        ->whereDate('test_date', '>', now()->endOfDay()->addWeekdays(3))
        ->get();

        if (!filled($users)) {
            Log::notice('No Users - '.now()->toDateTimeString());
            return;
        }

        // allowed visits per hour split between people and limited to >= 1
        $frequency = round( 60 / (315 / count($users) ) - 0.499 ) ?: 1;

        $users->load(['locations' => function($location) use ($frequency) {
            return $location->where('last_checked', '<', now()->subMinutes($frequency)->timestamp);
        }]);

        $schedule->call(function() use ($users) {
            $locations = $users->pluck('locations')->flatten()->pluck('name')->unique()->flip();
            $best_users = (new User)->getBest($users, $locations);

            $random = str_random(3);

            \Log::info('Starting :' . $random);
            \Log::info($best_users);

            foreach ($best_users as $user) {
                ScrapeDVSA::dispatch($user, $random)->onConnection('redis');
            }
        })->cron("*/{$frequency} * * * *")
            ->name('DVSA')
            ->withoutOverlapping();
//          ->unlessBetween('23:00', '6:00');
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
