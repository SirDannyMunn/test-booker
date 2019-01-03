<?php

namespace App\Jobs;

use App\User;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Notifications\ReservationMade;

class SendNotifications
{
    use Dispatchable, SerializesModels;

    private $data;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $users = User::all();
        
        foreach ($data as $item) { /* @var $user User*/ /* @var $item Collection * collection of users and slots, ranked and sorted to acquire best match */ 
            $user->notify(new ReservationMade($data['user'], $data['item']));
        }
    }
}
