<?php

namespace App\Console\Commands\SB;

use App\Jobs\Bets\ProcessBet;
use App\Models\Bets\Bet;
use Illuminate\Console\Command;

class OpenBetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sb:open-bets';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run jobs to process open bets.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $bets = Bet::open()
            ->with([
                'user', 'wallet', 'selections', 'selections.market',
                'selections.marketOdd', 'selections.match',
                'selections.match.result', 'selections.match.home',
                'selections.match.away', 'selections.match.stats',
            ])
            ->get();

        $bar = $this->output->createProgressBar($bets->count());

        foreach ($bets as $bet) {
            ProcessBet::dispatch($bet, $bet->user);

            $bar->advance();
        }

        $bar->finish();

        $this->info(" \n {$bets->count()} bets are processing right now!");

        return 0;
    }
}
