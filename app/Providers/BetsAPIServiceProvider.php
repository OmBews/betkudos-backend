<?php

namespace App\Providers;

use App\BetsAPI\Bet365\Bet365Client;
use App\Contracts\BetsAPI\Bet365\Bet365ClientInterface;
use Illuminate\Support\ServiceProvider;

class BetsAPIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(Bet365ClientInterface::class, function () {
            return new Bet365Client(config('betsapi.token'));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
