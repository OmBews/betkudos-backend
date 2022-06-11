<?php

namespace App\Console\Commands\SB\Upcoming;

use App\Contracts\Repositories\MatchRepository;
use App\Contracts\Repositories\SportsRepository;
use App\Contracts\Services\BetsAPI\Bet365ServiceInterface;
use App\Exceptions\BetsAPI\APICallException;
use App\Http\Clients\BetsAPI\Responses\Bet365\UpcomingResponse;
use App\Jobs\Matches\ProcessResults;
use App\Jobs\Matches\ProcessUpcoming;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use Illuminate\Console\Command;

use function Amp\Promise\wait;

class MatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sb:upcoming-matches
                            {sport : The id of the sport}
                            {--all : Find for new upcoming events and update next-matches and pre-matches}
                            {--past : Update past matches results}
                            {--find-new : Find for new upcoming events}
                            {--update= : Witch events will be updated (next-matches || pre-matches)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Search for new upcoming matches';

    /**
     * Odds provider API client.
     *
     * @var Bet365ServiceInterface
     */
    protected $service;

    /**
     * Sport to be handled.
     *
     * @var Sport
     */
    protected $sport;

    /**
     * Sports repository.
     *
     * @var SportsRepository
     */
    protected $sportsRepository;

    /**
     * Match repository.
     *
     * @var MatchRepository
     */
    protected $matchRepository;

    public const UPDATE_NEXT_MATCHES = 'next-matches';
    public const UPDATE_PRE_MATCHES = 'pre-matches';

    protected const UPDATE_OPTIONS = [
      self::UPDATE_PRE_MATCHES,
      self::UPDATE_NEXT_MATCHES
    ];

    /**
     * Execute the console command.
     *
     * @param Bet365ServiceInterface $service
     * @param SportsRepository $repository
     * @param MatchRepository $matches
     * @return int
     * @throws \Throwable
     */
    public function handle(Bet365ServiceInterface $service, SportsRepository $repository, MatchRepository $matches)
    {
        $this->service = $service;
        $this->sportsRepository = $repository;
        $this->matchRepository = $matches;

        try {
            if (! empty($error = $this->validateArgs())) {
                $this->error($error);
                return 1;
            }

            $this->findSport();

            if (! empty($error = $this->validateSport())) {
                $this->error($error);
                return 1;
            }

            if ($this->option('all')) {
                $this->all();
            } elseif ($this->option('past')) {
                $this->updatePastMatches();
            } elseif ($this->option('find-new')) {
                $this->findNewMatches();
            } elseif ($this->option('update')) {
                $this->runUpdateMatches();
            }

            return 0;
        } catch (\Exception | \Throwable $exception) {
            $this->error($exception->getMessage());
            return 1;
        }
    }

    /**
     * @throws \Throwable
     */
    protected function all()
    {
        $this->findNewMatches();
        $this->updateNextMatches();
        $this->updatePreMatches();
        $this->updatePastMatches();
    }

    /**
     * @throws \Throwable
     */
    protected function findNewMatches()
    {
        $timeFrame = $this->sport->getTimeFrame();

        $this->info("Searching for new {$this->sport->name} upcoming matches");
        $this->info("in the next $timeFrame days");

        $sportId = $this->sport->getKey();

        $bar = $this->output->createProgressBar($timeFrame);
        $bar->start();

        $bar->setMessage('Requesting data...');

        $promises = [];
        for ($days = 0; $days <= $timeFrame; $days++) {
            $date = date('Y-m-d', strtotime("+$days days"));

            $bar->setMessage("Searching for matches in $date");

            $promises[$days] = $this->service->makeUpcomingRequest($days, $sportId);

            $bar->advance();
        }

        $responses = wait(\Amp\Promise\all($promises));

        $bar->finish();

        foreach ($responses as $response) {
            $this->handleUpcomingResponse($response);
        }

        $this->info("All jobs was dispatched");
    }


    /**
     * @param UpcomingResponse $response
     */
    private function handleUpcomingResponse(UpcomingResponse $response)
    {
        foreach ($response->getMatches() as $match) {
            ProcessUpcoming::dispatch($match);
        }

        $this->resolveNextPages($response);
    }

    /**
     * @param UpcomingResponse $response
     */
    private function resolveNextPages(UpcomingResponse $response)
    {
        if (! $response->hasMorePages()) {
            return;
        }

        try {
            $date = date('Y-m-d', strtotime($response->getDay()));

            $this->warn("Searching for matches in $date at page {$response->nextPage()}");

            $promise = $this->service->makeUpcomingRequest(
                $response->getDay(),
                $response->getSportId(),
                $response->nextPage(),
                $response->getLeagueId()
            );

            $upcomingResponse = wait($promise);

            $this->handleUpcomingResponse($upcomingResponse);
        } catch (APICallException $APICallException) {
            $this->error($APICallException->getMessage());
        } catch (\Exception | \Throwable $e) {
            $this->error($e->getMessage());
        }
    }

    /**
     * @return void
     */
    private function runUpdateMatches()
    {
        if ($this->option('update') === self::UPDATE_NEXT_MATCHES) {
            $this->updateNextMatches();
            return;
        }

        $this->updatePreMatches();
    }

    /**
     * @return void
     */
    private function updateNextMatches()
    {
        $this->warn('updating next matches');

        $matches = $this->matchRepository->nextMatches(
            $this->sport->getKey()
        );

        $this->warn('Dispatching next matches jobs...');

        $bar = $this->output->createProgressBar($matches->count());

        $bar->start();

        foreach ($matches as $match) {
            ProcessResults::dispatch($match);

            $bar->advance();
        }

        $this->info('All next matches jobs was dispatched');

        $bar->finish();
    }

    /**
     * @return void
     */
    private function updatePreMatches()
    {
        $this->warn('updating pre matches');

        $matches = $this->matchRepository->preMatches(
            $this->sport->getKey()
        );

        $this->warn('Dispatching pre matches jobs...');

        $bar = $this->output->createProgressBar($matches->count());

        $bar->start();

        foreach ($matches as $match) {
            ProcessResults::dispatch($match);

            $bar->advance();
        }

        $this->info('All pre matches jobs was dispatched');

        $bar->finish();
    }

    /**
     * @return void
     */
    private function updatePastMatches()
    {
        $this->warn('updating past matches');

        $matches = Event::query()
            ->where('starts_at', '>=', strtotime('-12 hours'))
            ->where('starts_at', '<=', time())
            ->whereIn('time_status', [Event::STATUS_IN_PLAY, Event::STATUS_NOT_STARTED])
            ->get();

        $this->warn('Dispatching past matches jobs...');

        $bar = $this->output->createProgressBar($matches->count());

        $bar->start();

        foreach ($matches as $match) {
            ProcessResults::dispatch($match);

            $bar->advance();
        }

        $this->info('All past matches jobs was dispatched');

        $bar->finish();
    }

    /**
     * @return Sport|null
     */
    private function findSport(): ?Sport
    {
        $sportId = $this->argument('sport');

        if (is_null($this->sport)) {
            $this->sport = $this->sportsRepository->find($sportId);
        }

        return $this->sport;
    }

    /**
     * @return string
     */
    private function validateSport(): string
    {
        if (is_null($this->sport)) {
            return "Sport not found";
        }

        if (! $this->sport->isActive()) {
            return "The {$this->sport->name} sport is currently blocked";
        }

        return '';
    }

    /**
     * @return string
     */
    protected function validateArgs(): string
    {
        $sportId = $this->argument('sport');

        if (! is_numeric($sportId)) {
            return 'A valid sport ID is required';
        }

        $update = $this->option('update');

        if ($update && ! in_array($update, self::UPDATE_OPTIONS)) {
            return "The update option provided is invalid: $update";
        }

        return '';
    }
}
