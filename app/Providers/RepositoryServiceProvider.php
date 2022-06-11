<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    private const CONTRACT_NAMESPACE = 'App\Contracts\Repositories';

    private const NAMESPACE = 'App\Repositories';

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->bind('FaqRepository');
        $this->bind('PromotionRepository');
        $this->bind('UserRepository');
        $this->bind('SessionRepository');
        $this->bind('SessionLogRepository');
        $this->bind('DeviceRepository');
        $this->bind('NotAllowedIpRepository');
        $this->bind('SportsRepository');
        $this->bind('MatchRepository');
        $this->bind('LeagueRepository');
        $this->bind('TeamRepository');
        $this->bind('ResultRepository');
        $this->bind('OddsRepository');
        $this->bind('BetRepository');
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

    private function bind(string $name)
    {
        $abstract = self::CONTRACT_NAMESPACE . '\\' . $name;
        $concrete = self::NAMESPACE . '\\' . $name;

        $this->app->bind($abstract, $concrete);
    }
}
