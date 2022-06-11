<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountChanges extends Notification
{
    use Queueable;

    private $ipAddress;

    /**
     * Create a new notification instance.
     *
     * @param string $attributes
     * @param string $ipAddress
     */
    public function __construct(string $ipAddress)
    {
        $this->ipAddress = $ipAddress;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        $messageParams = [
            'ipAddress' => $this->ipAddress
        ];

        return (new MailMessage())
                    ->subject(trans('auth.notifications.changes.subject'))
                    ->line(trans('auth.notifications.changes.message', $messageParams))
                    ->line(trans('auth.notifications.changes.no_further_action'));
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
