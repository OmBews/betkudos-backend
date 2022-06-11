<?php

namespace App\Notifications\Auth;

use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\URL;

class EmailVerification extends Notification implements ShouldQueue
{
    use Queueable;

    private string $product;

    /**
     * Create a new notification instance.
     *
     * @param string $queue
     */
    public function __construct(string $product = 'mobile')
    {
        if (config('queue.custom_names') && ! $this->queue) {
            $this->onQueue('emails');
        }

        $this->product = $product;
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
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $routeParams = [
            'id' => $notifiable->id,
            'hash' => sha1($notifiable->email),
            'product' => $this->product
        ];

        $routeName = 'api.verify-email';

        $action = URL::temporarySignedRoute($routeName, now()->addHour(), $routeParams);

        return (new MailMessage())
                    ->greeting("Hello $notifiable->username")
                    ->line('Welcome to ' . config('app.name'))
                    ->line('Verify your email by clicking in the button bellow:')
                    ->action('Verify', $action)
                    ->line('This link will expire in 60 minutes')
                    ->line('Good luck and ENJOY!');
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
