<?php

namespace App\Console\Commands;

use App\Models\Currencies\CryptoCurrency;
use App\Models\Users\User;
use App\Services\WalletService;
use Illuminate\Console\Command;

class CreateUserWallets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:wallets {username}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create user default wallets';

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
    public function handle()
    {
        $user = $this->getUserByUsername($this->username());

        $currencies = CryptoCurrency::all();
        $walletService = new WalletService();

        $this->info('Creating wallets for user: ' . $this->username());
        $bar = $this->output->createProgressBar();
        $bar->start($currencies->count());

        foreach ($currencies as $currency) {
            $walletService->createWallet($user, $currency);

            $bar->advance();
        }

        $bar->finish(); echo PHP_EOL;

        $this->info('Wallets created successfully');

        return 0;
    }

    private function username()
    {
        return $this->argument('username');
    }

    protected function getUserByUsername(string $username): User
    {
        return User::where('username', $username)->first();
    }
}
