<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Auth\Notifications\ResetPassword as Notification;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class ResetPassword extends Notification
{
    use Queueable;

    public Agent $agent;

    /**
     * Create a new notification instance.
     *
     * @param $token
     * @param Agent $agent
     */
    public function __construct($token, Agent $agent)
    {
        parent::__construct($token);

        $this->agent = $agent;
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
        $clientResetPath = config('auth.client.reset_password_path');
        $clientResetPath = Str::replaceFirst(':username', $notifiable->username, $clientResetPath);
        $clientResetPath = Str::replaceFirst(':email', $notifiable->email, $clientResetPath);
        $clientResetPath = Str::replaceFirst(':token', $this->token, $clientResetPath);

        $url = $this->agent->isPhone() || $this->agent->isTablet() ?
            url(config('app.frontend_url') . $clientResetPath) :
            url(config('app.frontend_desktop') . $clientResetPath);

        $expiresIn = ['count' => config('auth.passwords.' . config('auth.defaults.passwords') . '.expire')];

        return (new MailMessage())
            ->subject(trans('passwords.notifications.reset.subject'))
            ->line(trans('passwords.notifications.reset.reason'))
            ->action(trans('passwords.notifications.reset.action'), $url)
            ->line(trans('passwords.notifications.reset.expires_in', $expiresIn))
            ->line(trans('passwords.notifications.reset.no_further_action'));
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
