<?php

namespace App\Console\Commands;

use App\Services\SlotegratorGamesService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SlotegratorGames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'slotegrator:games';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle(SlotegratorGamesService $service)
    {
        $service->storeGames();

        return 0;
    }
}
