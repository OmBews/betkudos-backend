<?php

namespace App\Providers;

use App\Events\Auth\EmailUpdated;
use App\Events\Auth\Google2faDisabled;
use App\Events\Auth\Google2faEnabled;
use App\Events\Auth\Login;
use App\Events\Auth\Logout;
use App\Events\Auth\PasswordUpdated;
use App\Events\Events\Ended;
use App\Events\Events\Failed;
use App\Events\Events\Rescheduled;
use App\Events\Events\Started;
use App\Listeners\Auth\LogSession;
use App\Listeners\Events\LiveEventEnded;
use App\Listeners\Events\LiveEventFailed;
use App\Listeners\Events\LiveEventRescheduled;
use App\Listeners\Events\LiveEventStarted;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\Events\AccessTokenCreated;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\Auth\Registered' => [
            LogSession::class
        ],
        Login::class => [
            LogSession::class
        ],
        Logout::class => [
            LogSession::class
        ],
        PasswordUpdated::class => [
            LogSession::class
        ],
        EmailUpdated::class => [
            LogSession::class
        ],
        Google2faEnabled::class => [
            LogSession::class
        ],
        Google2faDisabled::class => [
            LogSession::class
        ],
        Started::class => [
            LiveEventStarted::class,
        ],
        Rescheduled::class => [
            LiveEventRescheduled::class,
        ],
        Ended::class => [
            LiveEventEnded::class,
        ],
        Failed::class => [
            LiveEventFailed::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
