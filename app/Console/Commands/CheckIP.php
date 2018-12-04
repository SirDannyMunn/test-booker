<?php

namespace App\Console\Commands;

use App\Browser\Browser;
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
        (new Browser)->browse(function($window) {

            $protocol = $this->option('s') ? 'https' : 'http';
//            $this->line($protocol);

//            $window->visit('https://www.iplocation.net/');
//            $window->visit("{$protocol}://ipinfo.info/html/ip_checker.php");
            $window->visit('https://ipinfo.info/html/ip_checker.php');
//            $this->line("At page");
            $window->screenshot("IP " . now()->format('H.i.s'));
            $url = $window->getUrl();
            $this->line($url);

//            $window->type('.gLFyf', 'Whats my ip');
//            $ip = $window->text('.MUxGbd');

            $ip = $window->text('#Text14 > p > span > a > b');
            $this->line($ip);
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(11) > div > table'));
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(12) > div > table'));
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(13) > div > table'));
//            $this->info($window->text('#wrapper > section > div > div > div.col.col_8_of_12 > div:nth-child(14) > div > table'));

            $window->quit();
        });
    }
}
