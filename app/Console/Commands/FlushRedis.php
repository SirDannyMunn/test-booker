<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FlushRedis extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'redis:flush {connection=default} {--run}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Flushes Redis';

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
     */
    public function handle()
    {
        $this->line('Flushing...');
        $this->line(\Illuminate\Support\Facades\Redis::connection('default')->dbsize());
        Redis::connection($this->argument('connection'))->flushdb();
        Redis::connection($this->argument('connection'))->flushall();
        $this->line('Flushed.');
        $this->line(\Illuminate\Support\Facades\Redis::connection('default')->dbsize());

        if ($this->option('run')) {
            $this->call('horizon');
        }
    }
}
