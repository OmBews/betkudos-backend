<?php

namespace App\Jobs\Matches;

use App\Contracts\Repositories\SportsRepository;
use App\Contracts\Services\FeedService;
use App\Models\Sports\Sport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class DispatchUpcomingMatchesJobs implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        $this->queue = 'default';
        $this->connection = 'redis';
    }

    /**
     * Execute the job.
     *
     * @param FeedService $feedService
     * @return void
     */
    public function handle(FeedService $feedService)
    {
        $feedService->forgetPreviewCache();

        foreach (Sport::active()->get() as $sport) {
            Artisan::queue("sb:upcoming-matches {$sport->getkey()} --all")->onQueue('default');
            Artisan::queue("sb:upcoming-odds {$sport->getkey()}")->onQueue('default');
        }
    }
}
