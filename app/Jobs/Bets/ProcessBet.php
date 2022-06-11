<?php

namespace App\Jobs\Bets;

use App\Contracts\Services\BetProcessorService;
use App\Models\Bets\Bet;
use App\Models\Users\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBet implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Bet
     */
    private $bet;

    /**
     * @var User
     */
    private $user;

    /**
     * Create a new job instance.
     *
     * @param Bet $bet
     * @param User $user
     */
    public function __construct(Bet $bet, User $user)
    {
        $this->bet = $bet;
        $this->user = $user;

        if (config('queue.custom_names') && ! $this->queue) {
            $this->onQueue('process-bets');
        }
    }

    /**
     * Execute the job.
     *
     * @param BetProcessorService $service
     * @return void
     */
    public function handle(BetProcessorService $service)
    {
        $service->process($this->bet);
    }
}
