<?php

namespace App\Notifications;

use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationMade extends Notification
{
    use Queueable;

    protected $user;
    protected $date;
    protected $location;
    protected $actionCode;

    public function __construct($user, $slot)
    {
        $this->user = $user;
        $this->location = $slot['location'];
        $this->date = Carbon::parse($slot['date'])->format('d/m/y h:m');
        $this->actionCode = uniqid() . str_random(10);

        $this->user->action_code = $this->actionCode;
        $this->user->save();
    }

    public function via($notifiable)
    {
        if ($this->user->contact_preference == 'sms')
            return ['nexmo'];

        return ['mail'];
    }

    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content('Howdy Partner')
            ->from(env('NEXMO_FROM'));
    }

    public function toMail($notifiable)
    {
        $test_date = Carbon::parse($this->user->test_date)->format('d/m/y h:m');

        return (new MailMessage)
                ->greeting("Hi {$this->user->name}!")
                ->line("Your test is on at {$test_date}")
                ->line("We have a test available at {$this->date} at {$this->location} test centre")
                ->line("If you would like this date, please click the button below.
                 Otherwise, ignore this message and we will send you another date when one comes up.")
                ->action('Book', url("user/accept_booking?user={$this->actionCode}"))
                ->line('Thank you for using our service!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
