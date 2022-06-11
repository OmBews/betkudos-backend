<?php

namespace App\Providers;

use App\Models\Events\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Connection;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(
            \App\Contracts\Http\Clients\BetsAPI\Bet365ClientInterface::class,
            \App\Http\Clients\BetsAPI\Bet365Client::class
        );
        $this->app->bind(
            \App\Contracts\Services\BetsAPI\Bet365ServiceInterface::class,
            \App\Services\BetsAPI\Bet365Service::class
        );
        $this->app->bind(
            \App\Contracts\Services\BettingService::class,
            \App\Services\BettingService::class
        );
        $this->app->bind(
            \App\Contracts\Services\FeedService::class,
            \App\Services\FeedService::class
        );
        $this->app->bind(
            \App\Contracts\Services\BetProcessorService::class,
            \App\Services\BetProcessorService::class
        );
        $this->app->bind(
            \App\Contracts\Services\BetSlipService::class,
            \App\Services\BetSlipService::class
        );
        $this->app->bind(
            \App\Contracts\Services\CasinoServiceInterface::class,
            \App\Services\CasinoService::class
        );
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading();
    }
}
