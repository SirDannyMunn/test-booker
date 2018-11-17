<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\NexmoMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class ReservationMade extends Notification
{
    use Queueable;

    protected $user;
    protected $date;

    /**
     * Create a new notification instance.
     *
     * @param $user
     * @param $date
     */
    public function __construct($user, $date)
    {
        $this->user = $user;
        $this->date = $date;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        if ($this->user->contact_preference == 'sms')
            return ['nexmo'];

        return ['mail'];
    }


    /**
     * @param $notifiable
     * @return NexmoMessage
     */
    public function toNexmo($notifiable)
    {
        return (new NexmoMessage)
            ->content('Howdy Partner')
            ->from(env('NEXMO_FROM'));
    }


    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
                ->greeting('Howdy!')
                ->line("We have a test available on the {$this->date}")
                ->line("If you would like this date, please click the button below.
                 Otherwise, ignore this message and we will send you another date when one comes up.")
                ->action('Book', url('/'))
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
