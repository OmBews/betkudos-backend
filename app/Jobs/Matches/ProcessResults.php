<?php

namespace App\Jobs\Matches;

use App\Exceptions\BetsAPI\APICallException;
use App\Models\Countries\Country;
use App\Models\Leagues\League;
use App\Models\Events\Event;
use App\Models\Events\Results\Result;
use App\Models\Events\Stats\Stats;
use App\Models\Teams\Team;
use App\Services\MatchEventsService;
use App\Services\MatchResultService;
use App\Services\ScoreService;
use Carbon\Carbon;
use Carbon\Exceptions\Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessResults implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Event
     */
    private $match;

    /**
     * Create a new job instance.
     *
     * @param Event $match
     */
    public function __construct(Event $match)
    {
        $this->match = $match;

        if (config('queue.custom_names') && ! $this->queue) {
            $this->onQueue('upcoming-results');
        }

        $areTeamsLoaded = $this->match->relationLoaded('home') && $this->match->relationLoaded('away');
        $isLeagueLoaded = $this->match->relationLoaded('league');
        $isResultLoaded = $this->match->relationLoaded('result');
        $isStatsLoaded = $this->match->relationLoaded('stats');

        if (!$isLeagueLoaded || !$areTeamsLoaded || !$isResultLoaded || !$isStatsLoaded) {
            $this->match->load(['league', 'home', 'away', 'result', 'stats']);
        }
    }

    /**
     * Execute the job.
     *
     * @param MatchResultService $service
     * @return void
     * @throws \Throwable
     */
    public function handle(MatchResultService $service)
    {
        try {
            $data = $service->result($this->match->getBet365Id());

            $shouldUpdateLeague = $this->shouldUpdateResource($this->match->league);
            $shouldUpdateTeams =
                $this->shouldUpdateResource($this->match->home) &&
                $this->shouldUpdateResource($this->match->away);

            if ($shouldUpdateLeague) {
                $this->runLeagueUpdate($data);
            }

            if ($shouldUpdateTeams) {
                $this->runTeamsUpdate($data);
            }

            $this->runMatchUpdate($data);
            $this->runStatsAndResultsUpdate($data, $service);
        } catch (APICallException $exception) {
            if ($exception->getError() === APICallException::UNAVAILABLE_MATCH_RESULT) {
                // Log::error("Match Result JOB Error #{$this->match->getKey()}: {$exception->getMessage()}");
            }

            throw $exception;
        } catch (\Exception $exception) {
            throw $exception;
        }
    }

    private function shouldUpdateResource(Model $model): bool
    {
        $lastHour = now()->subHour();

        return $model->updated_at->lessThanOrEqualTo($lastHour) ||
            $model->created_at->greaterThanOrEqualTo($lastHour);
    }

    /**
     * @param $data
     * @throws \Exception
     */
    private function runTeamsUpdate($data)
    {
        $homeTeamId = $this->match->home_team_id;
        $awayTeamId = $this->match->away_team_id;

        $updateHomeTeam = function () use ($data, $homeTeamId) {
            $homeData = [
                'bets_api_id' => $data->home->id,
                'image_id' => $data->home->image_id,
                'cc' => $data->home->cc,
            ];

            Team::query()->where('bet365_id', $homeTeamId)->update($homeData);
        };

        $updateAwayTeam = function () use ($data, $awayTeamId) {
            $away = [
                'bets_api_id' => $data->away->id,
                'image_id' => $data->away->image_id,
                'cc' => $data->away->cc,
            ];

            Team::query()->where('bet365_id', $awayTeamId)->update($away);
        };

        retry(10, $updateHomeTeam, 200);
        retry(10, $updateAwayTeam, 200);
    }

    /**
     * @param $data
     * @throws \Exception
     */
    private function runLeagueUpdate($data)
    {
        $updateLeague = function () use ($data) {
            if (!property_exists($data, 'league')) {
                return;
            }

            $league = [
                'bets_api_id' => $data->league->id,
                'cc' => !$this->match->league->cc ?
                    $data->league->cc ?? Country::TEMPORARY_COUNTRY_CODE :
                    $this->match->league->cc,
            ];

            $this->match->league->update($league);
        };

        retry(10, $updateLeague, 200);
    }

    /**
     * @param $data
     * @throws \Exception
     */
    private function runMatchUpdate($data)
    {
        $updateMatch = function () use ($data) {
            $match = [
                'cc' => property_exists($data, 'league') ?
                    $data->league->cc : $this->match->league->cc ?? Country::TEMPORARY_COUNTRY_CODE,
                'time_status' => $data->time_status,
                'starts_at' => property_exists($data, 'time') ? $data->time : $this->match->starts_at
            ];

            if ($this->match->isLive()) {
                $match['last_bets_api_update'] = time();
            }

            $this->match->update($match);
        };

        retry(10, $updateMatch, 200);
    }

    /**
     * @param $data
     * @param MatchResultService $service
     * @throws \Exception
     */
    private function runStatsAndResultsUpdate($data, MatchResultService $service)
    {
        $matchId = $this->match->getKey();
        $stats = $service->statsToArrayModel($data);
        $results = $service->resultToArrayModel($data);

        $updateStats = function () use ($stats, $matchId) {
            Stats::query()->upsert(array_merge(['match_id' => $matchId], $stats), ['match_id'], ['stats', 'events']);
        };

        $resultsFieldsToUpdate = [
            'scores',
            'is_playing',
            'passed_minutes',
            'passed_seconds',
            'current_time',
            'quarter',
        ];

        // Prevents us getting delayed score data
        if ($this->match->result && (isset($results['single_score']) && $results['single_score'])) {
            $scores = ScoreService::factory($results['single_score'])->scores();

            if (count($scores) === 2) {
                [$home, $away] = ScoreService::factory($results['single_score'])->scores();

                $homeTeamScoreCacheKey = "event_{$this->match->getKey()}_home_team_score";
                $awayTeamScoreCacheKey = "event_{$this->match->getKey()}_away_team_score";

                $homeTeamCachedScore = Cache::get($homeTeamScoreCacheKey, 0);
                $awayTeamCachedScore = Cache::get($awayTeamScoreCacheKey, 0);

                $thereIsScoreChanges = (int) $home > $homeTeamCachedScore || (int) $away > $awayTeamCachedScore;

                if ($thereIsScoreChanges || !$this->match->result->single_score) {
                    $resultsFieldsToUpdate = array_merge($resultsFieldsToUpdate, ['single_score']);

                    // Caches the scores by 60 seconds
                    Cache::put($homeTeamScoreCacheKey, (int) $home, 60);
                    Cache::put($awayTeamScoreCacheKey, (int) $away, 60);
                }
            }
        }

        if (($this->match->stats && $this->match->result) && (isset($results['points']) && $results['points'])) {
            $points = ScoreService::factory($results['points'])->scores();

            if (count($points) === 2) {
                [$home, $away] = $points;

                $game = MatchEventsService::factory($this->match->stats->events)->currentGame();

                $homeTeamPointsCacheKey = "event_{$this->match->getKey()}_home_team_game_{$game}_points";
                $awayTeamPointsCacheKey = "event_{$this->match->getKey()}_away_team_game_{$game}_points";

                $homeTeamCachedPoints = Cache::get($homeTeamPointsCacheKey, 0);
                $awayTeamCachedPoints = Cache::get($awayTeamPointsCacheKey, 0);

                $thereIsPointsChanges = (int) $home > $homeTeamCachedPoints || (int) $away > $awayTeamCachedPoints;

                if ($thereIsPointsChanges || !$this->match->result->points || ($homeTeamCachedPoints === 0 && $awayTeamCachedPoints === 0)) {
                    $resultsFieldsToUpdate = array_merge($resultsFieldsToUpdate, ['points']);

                    // Caches the points by 30 seconds
                    Cache::put($homeTeamPointsCacheKey, (int) $home, 30);
                    Cache::put($awayTeamPointsCacheKey, (int) $away, 30);
                }
            }
        }

        $updateResults = function () use ($resultsFieldsToUpdate, $results, $matchId) {
            Result::query()->upsert(array_merge(['match_id' => $matchId], $results), ['match_id'], $resultsFieldsToUpdate);
        };

        retry(10, $updateStats, 200);
        retry(10, $updateResults, 200);
    }
}
