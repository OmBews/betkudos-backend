<?php

namespace App\Jobs\Matches;

use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Models\Teams\Team;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessUpcoming implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var UpcomingMatch
     */
    private $upcomingMatch;

    public $tries = 3;

    /**
     * Create a new job instance.
     *
     * @param UpcomingMatch $upcomingMatch
     */
    public function __construct(UpcomingMatch $upcomingMatch)
    {
        $this->upcomingMatch = $upcomingMatch;

        if (config('queue.custom_names') && ! $this->queue) {
            $this->onQueue('upcoming-matches');
        }
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws \Exception
     */
    public function handle()
    {
        $this->runUpdateOrCreateLeagueIfNotExists();
        $this->runUpdateOrCreateTeamsIfNotExists();
        $this->runUpdateOrCreateMatch();
    }

    /**
     * @throws \Exception
     */
    private function runUpdateOrCreateLeagueIfNotExists()
    {
        $leagueId = $this->upcomingMatch->getLeague()->id;
        $leagueExists = League::query()->where('bet365_id', $leagueId)->exists();

        $leagueNameExist = League::query()->where('name', $this->upcomingMatch->getLeague()->name)->exists();

        if ($leagueExists) {
            return;
        }

        if ($leagueNameExist) {
            return;
        }

        $handler = function () use ($leagueId) {
            $league = [
                'bet365_id' => $leagueId,
                'name' => $this->upcomingMatch->getLeague()->name,
                'sport_id' => $this->upcomingMatch->getSportId()
            ];

            League::query()->updateOrCreate($league);
        };

        retry(10, $handler, 200);
    }

    /**
     * @throws \Exception
     */
    private function runUpdateOrCreateTeamsIfNotExists()
    {
        $homeTeamId = $this->upcomingMatch->getHome()->id;
        $awayTeamId = $this->upcomingMatch->getAway()->id;

        $home = [
            'name' => $this->upcomingMatch->getHome()->name,
            'bet365_id' => $homeTeamId,
        ];

        $away = [
            'name' => $this->upcomingMatch->getAway()->name,
            'bet365_id' => $awayTeamId,
        ];

        $handler = function () use ($awayTeamId, $homeTeamId, $home, $away) {
            if (! $this->teamExists($homeTeamId)) {
                Team::query()->updateOrCreate($home);
            }

            if (! $this->teamExists($awayTeamId)) {
                Team::query()->updateOrCreate($away);
            }
        };

        retry(10, $handler, 200);
    }

    private function teamExists($teamId): bool
    {
        return Team::query()->where('bet365_id', $teamId)->exists();
    }

    /**
     * @throws \Exception
     */
    private function runUpdateOrCreateMatch()
    {
        $bet365Id = $this->upcomingMatch->getId();
        $sportId = $this->upcomingMatch->getSportId();

        $attrs = [
            'bets_api_id' => $this->upcomingMatch->getBetsAPIId(),
            'home_team_id'  => $this->upcomingMatch->getHome()->id,
            'away_team_id'  => $this->upcomingMatch->getAway()->id,
            'league_id'  => $this->upcomingMatch->getLeague()->id,
            'starts_at'  => $this->upcomingMatch->getTime(),
            'time_status'  => $this->upcomingMatch->getTimeStatus(),
            'last_bets_api_update'  => $this->upcomingMatch->getUpdatedAt(),
        ];

        $handler = function () use ($sportId, $bet365Id, $attrs) {
            $match = Event::query()->updateOrCreate(['bet365_id' => $bet365Id, 'sport_id' => $sportId], $attrs);

            ProcessPreMatchOdds::dispatch($match);
            ProcessResults::dispatch($match);
        };

        retry(10, $handler, 200);
    }
}
