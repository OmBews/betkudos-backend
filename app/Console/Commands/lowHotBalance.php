<?php

namespace App\Console\Commands;

use App\Models\RiskMonitor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Nexmo\Laravel\Facade\Nexmo;
use App\Blockchain\CryptoWallet;

class lowHotBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lowHotBalance:cron';

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
    public function handle()
    {
        // check attempts
        try {
            $cw = new CryptoWallet;
            $balance = $cw->get_balance('btc');

            if ($balance) {
                $riskMonitor = RiskMonitor::WhereDate('created_at', date('Y-m-d'))->first();

                if (!$riskMonitor) {
                    $riskMonitor = new RiskMonitor();
                    $riskMonitor->amount = $balance;
                    $riskMonitor->attempts = 1;
                    $riskMonitor->save();
                }

                $percentValue = (100 - ($balance / $riskMonitor->amount) * 100);

                if ($percentValue > 50) {

                    // Message Integration 'Hot Wallet - Red Alert' to - +447545 841 937
                    if ($riskMonitor->attempts < 4) {
                        Nexmo::message()->send([
                            'to'   => env('NOTIFICATION_NUMBER'),
                            'from' => 'Betkudos',
                            'text' => 'Hot Wallet - Red Alert.'
                        ]);

                        // Update attemps / only 3 message should send
                        $rm = RiskMonitor::find($riskMonitor->id);
                        $rm->attempts = ($riskMonitor->attempts + 1);
                        $rm->save();
                    }
                } else {
                    // Update attemps 1 to resend SMS if balance will be low
                    $rm = RiskMonitor::find($riskMonitor->id);
                    $rm->attempts = 1;
                    $rm->save();
                }
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
