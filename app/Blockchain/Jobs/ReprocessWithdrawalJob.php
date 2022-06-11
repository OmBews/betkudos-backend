<?php

namespace App\Blockchain\Jobs;

use App\Blockchain\CryptoWallet;
use App\Models\Withdrawals\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ReprocessWithdrawalJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cw = new CryptoWallet();

        $withdrawals = Withdrawal::with(['wallet', 'wallet.currency'])->where('status', 0)->get();

        foreach ($withdrawals as $withdraw) {
            $wd_currency = strtolower($withdraw->wallet->currency->ticker);
            $wd_txid = $cw->create_withdrawal($wd_currency, $withdraw->address, $withdraw->amount);

            if ($wd_txid === false) {
                //Unable to process Withdrawal
                $withdraw->error = $cw->cwerror;
                $withdraw->save();
            } else {
                $withdraw->status = 1;
                $withdraw->txid = $wd_txid;
                $withdraw->save();
            }
        }
    }
}
