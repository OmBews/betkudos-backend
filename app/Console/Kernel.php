<?php

namespace App\Console;

use App\Blockchain\Jobs\BtcBlocksJob;
use App\Blockchain\Jobs\EthBlocksJob;
use App\Blockchain\Jobs\EthCollectionJob;
use App\Blockchain\Jobs\ReprocessWithdrawalJob;
use App\Blockchain\Jobs\StoreEthNetworkFee;
use App\Contracts\Services\FeedService;
use App\Jobs\Matches\DispatchUpcomingMatchesJobs;
use App\Jobs\Matches\SearchForLiveMatches;
use App\Jobs\UpdateCryptoCurrenciesPrice;
use App\Models\Sports\Sport;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\RiskMonitor;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    private FeedService $feedService;

    public function __construct(Application $app, Dispatcher $events)
    {
        parent::__construct($app, $events);

        $this->feedService = new \App\Services\FeedService();
    }

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('horizon:snapshot')
            ->everyFiveMinutes()
            ->name('horizon:snapshot')
            ->onOneServer()->when(function () {
                return !config('app.serverless');
            });

        // $schedule->job(function () {
        //     EthBlocksJob::dispatch();
        //     EthBlocksJob::dispatch()->delay(now()->addSeconds(20));
        //     EthBlocksJob::dispatch()->delay(now()->addSeconds(40));
        // })->everyFifteenMinutes()->onOneServer();

        $schedule->job(BtcBlocksJob::class)->everyFifteenMinutes()->onOneServer();
        $schedule->job(ReprocessWithdrawalJob::class)->everyThirtyMinutes()->onOneServer();
        // $schedule->job(EthCollectionJob::class)->everyTenMinutes()->onOneServer();
        // $schedule->job(StoreEthNetworkFee::class)->everyFifteenMinutes()->onOneServer();
        $schedule->job(UpdateCryptoCurrenciesPrice::class)->everyThirtyMinutes()->onOneServer();

        $schedule->call(function () {
            $this->feedService->forgetPreviewCache();
        })
            ->name('forgetPreviewCache')
            ->onOneServer()
            ->hourly();

        $schedule->command('sb:open-bets')
            ->name('sb:open-bets')
            ->onOneServer()
            ->withoutOverlapping()
            ->everyTenMinutes();

        $schedule->command('telescope:prune')->name('telescope:prune')->onOneServer()->daily();

        if ((bool) setting('global.live_events_enabled', 1)) {
            $schedule->job(SearchForLiveMatches::class)->name('SearchForLiveEvents')->onOneServer()->everyMinute();
        }

        $schedule->command('lowHotBalance:cron')->name('lowHotBalance')->onOneServer()->everyFifteenMinutes();
        $this->scheduleUpcoming($schedule);
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }

    private function scheduleUpcoming(Schedule $schedule)
    {
        foreach (Sport::active()->get() as $sport) {
            $upcomingMatchesCommand = "sb:upcoming-matches {$sport->getkey()} --all";
            $upcomingOddsCommand = "sb:upcoming-odds {$sport->getkey()}";

            $schedule->command($upcomingMatchesCommand)->name($upcomingMatchesCommand)->onOneServer()->everyTwoHours();
            $schedule->command($upcomingOddsCommand)->name($upcomingOddsCommand)->onOneServer()->everyTwoHours();
        }
    }
}
