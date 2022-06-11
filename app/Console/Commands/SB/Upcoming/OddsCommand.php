<?php

namespace App\Console\Commands\SB\Upcoming;

use App\Contracts\Repositories\MatchRepository;
use App\Contracts\Repositories\SportsRepository;
use App\Jobs\Matches\ProcessPreMatchOdds;
use App\Models\Sports\Sport;
use Illuminate\Console\Command;

class OddsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sb:upcoming-odds
                            {sport : The id of the sport}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Searches for new Odds and update it';

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
     * @param SportsRepository $sports
     * @param MatchRepository $matches
     * @return int
     */
    public function handle(SportsRepository $sports, MatchRepository $matches)
    {
        $sportId = $this->argument('sport');

        if (! is_numeric($sportId)) {
            $this->error('Invalid sport id, try again.');

            return 1;
        }

        $sport = $sports->find($this->argument('sport'));

        if (! $sport instanceof Sport) {
            $this->error('Unable to find the provided sport');

            return 1;
        }

        $upcomingMatches = $matches->upcomingBySport($sport->getKey(), ['home', 'away']);

        foreach ($upcomingMatches as $match) {
            ProcessPreMatchOdds::dispatch($match)->delay(now()->addSeconds(rand(3, 300)));
        }

        $this->info("{$upcomingMatches->count()} jobs were dispatched");

        return 0;
    }
}
