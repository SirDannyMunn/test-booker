<?php

namespace App\Console\Commands;

use App\Browser\Browser;
use Carbon\Carbon;
use Illuminate\Console\Command;
use function PHPSTORM_META\type;

class CheckIP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'check:ip {--s}';

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
        $this->line('Starting');

        (new Browser)->browse(function($window) {

            $this->line('Browser opened');

            $protocol = $this->option('s') ? 'https' : 'http';
            $this->line($protocol);

            $window->visit("{$protocol}://ipinfo.info/html/ip_checker.php");

            $time1 = microtime(true);

            $url = $window->getUrl();
            $this->line($url);
            $window->screenshot(now()->format('h.i.s'));

            $ip = $window->text('#Text14 > p > span > a > b');
            $this->line($ip);

            $time2 = microtime(true);

            $this->line('script execution time: ' . ($time2 - $time1));

//            $window->visit('https://ipinfo.info/html/ip_checker.php');
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(11) > div > table'));
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(12) > div > table'));
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(13) > div > table'));
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(14) > div > table'));

            $window->quit();
        });
    }
}