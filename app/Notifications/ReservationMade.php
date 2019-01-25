<?php

namespace App\Notifications;

use App\Slot;
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
    protected $slot;
    private $actionUrl;

    public function __construct(User $user, Slot $slot)
    {
        $this->user = $user;
        $this->slot = $slot;
        $this->date = Carbon::parse($slot['date'])->format('d/m/y h:m');

        $actionCode = uniqid() . str_random(10);
        $this->actionUrl = url("user/accept_booking?user={$actionCode}");

        $this->user->action_code = $actionCode;
        $this->user->save();
    }

    public function via($notifiable)
    {
        return $this->user->contact_preference == 'sms' ? ['nexmo'] : ['mail'];
    }

    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content('Howdy Partner')
            ->from(env('NEXMO_FROM'));
    }

    public function toMail($notifiable)
    {
        $test_date = Carbon::parse($this->user->test_date);

        $message = new MailMessage;

        $message->greeting("Hi {$this->user->name}!")
            ->line("Your test is on at {$test_date->format('d/m/y h:m')}");

        if ($this->slot->withinLimit()) {
            $message->line("Warning: This test is within the three day limit. If you use this test, you may not be able to change it.");
        }

        $message->line("We have a test available at {$this->date} at {$this->slot->location} test centre")
            ->line("If you would like this date, please click the button below.
             Otherwise, ignore this message and we will send you another date when one comes up.")
            ->action('Book', $this->actionUrl)
            ->line('Thank you for using our service!');

        return $message;
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
