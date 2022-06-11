<?php

namespace App\Jobs;

use App\Services\SlotegratorGamesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RequestSlotegratorGames implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(private $page)
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(SlotegratorGamesService $service)
    {
        $games = $service->getGames($this->page)->items;

        foreach ($games as $game) {
            $service->storeGame($game);
        }
    }
}
