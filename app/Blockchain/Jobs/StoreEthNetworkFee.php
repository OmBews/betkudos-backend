<?php

namespace App\Blockchain\Jobs;

use App\Blockchain\CryptoWallet;
use App\Models\Currencies\CryptoCurrency;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreEthNetworkFee implements ShouldQueue
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
        $currency = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first(['id']);
        $cw = new CryptoWallet();

        $currency->network_fee = $cw->get_eth_wd_fees();
        $currency->save();
    }
}
