<?php

namespace App\Jobs\Matches;

use App\Events\Events\Ended;
use App\Events\Events\Failed;
use App\Events\Events\Rescheduled;
use App\Events\Events\Started;
use App\Models\Events\Event;
use App\Services\InPlayOddsService;
use App\Services\MatchResultService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class LiveMatchWorker implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Event
     */
    public Event $match;

    public string $owner;

    public $timeout = 3600; // 1 Hour

    public const IDLE_TIME = 3;
    private const EXECUTION_LIMIT = '+59 minutes';

    private WithoutOverlapping $withoutOverlapping;

    public $tries = 3;

    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 3600; // 1 Hour

    /**
     * Create a new job instance.
     *
     * @param Event $match
     * @param string $owner
     */
    public function __construct(Event $match, string $owner)
    {
        $this->connection = 'live-events';

        $this->match = $match;
        $this->owner = $owner;

        if (config('queue.custom_names') && ! $this->queue) {
            $this->onQueue("{$this->match->sport->name}-live-events");
        }
    }

    /**
     * Execute the job.
     *
     * @param MatchResultService $resultService
     * @param InPlayOddsService $oddsService
     * @throws \Exception
     */
    public function handle(MatchResultService $resultService, InPlayOddsService $oddsService)
    {
        $this->process($resultService, $oddsService);
    }

    public function process(MatchResultService $resultService, InPlayOddsService $oddsService): ?array
    {
        $attempts = 0;
        $executionLimit = strtotime(self::EXECUTION_LIMIT);
        $this->started();

        begin:
        try {
            while (time() < $executionLimit && $this->match->isEligibleToProcessLiveOdds()) {
                $start = time();
                $this->match->refresh();

                (new ProcessResults($this->match))->handle($resultService);

                if ($this->match->isLive()) {
                    (new ProcessLiveOdds($this->match))->handle($oddsService);
                } else {
                    ProcessPreMatchOdds::dispatchSync($this->match);
                }

                Cache::put("event_{$this->match->getKey()}_last_update", now());

                $end = time();

                $timeTaken = $end - $start;

                if (app('env') === 'local' || app('env') === 'staging') {
                    $status = $this->match->isLive() ? "Live - " : "Upcoming - ";
                    echo $status . $this->match->home->name. " v " .$this->match->away->name . " - Processed after $timeTaken seconds", PHP_EOL;
                }

                if ($timeTaken < self::IDLE_TIME) {
                    sleep(self::IDLE_TIME);
                }
            }

            if ($this->match->isEligibleToProcessLiveOdds()) {
                return $this->reschedule();
            }

            return $this->ended();
        } catch (\PDOException $exception) {
            if ($exception->getCode() === 1205) {
                $attempts++;

                if ($attempts < $this->tries) {
                    goto begin;
                }
            }

            $this->jobFailed();

            throw $exception;
        } catch (\Exception $exception) {
            $this->jobFailed();

            throw $exception;
        }
    }

    /**
     * The unique ID of the job.
     *
     * @return string
     */
    public function uniqueId()
    {
        return $this->match->getKey();
    }

    public function middleware()
    {
        $this->withoutOverlapping = (new WithoutOverlapping($this->match->getKey()))->dontRelease();

        return [$this->withoutOverlapping];
    }

    private function releaseLock()
    {
        $this->releaseWithoutOverlapLock();
        Cache::restoreLock("match_{$this->match->getKey()}_live_process", $this->owner)->release();
    }

    public function jobFailed()
    {
        event(new Failed($this->match));
    }

    private function started(): ?array
    {
        return event(new Started($this->match));
    }

    private function reschedule(): ?array
    {
        $this->releaseWithoutOverlapLock();

        LiveMatchWorker::dispatch($this->match, $this->owner);

        $this->restartWorker();

        return event(new Rescheduled($this->match));
    }

    private function ended()
    {
        $this->releaseLock();

        $this->restartWorker();

        return event(new Ended($this->match));
    }

    private function releaseWithoutOverlapLock()
    {
        Cache::lock($this->withoutOverlapping->getLockKey($this))->forceRelease();
    }

    private function restartWorker()
    {
        app('queue.worker')->shouldQuit = 1;
    }
}
