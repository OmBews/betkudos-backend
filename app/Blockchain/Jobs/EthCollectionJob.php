<?php

namespace App\Blockchain\Jobs;

use App\Blockchain\CryptoWallet;
use App\Models\Currencies\Collection;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Deposits\Deposit;
use App\Models\Wallets\WalletAddress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class EthCollectionJob implements ShouldQueue
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

        // GET ACTIVE WALLETS WITH BALANCE, STATUS AND GAS PRICE FROM DB

        $deposit_s = Deposit::with(['address'])
                        ->where('status', 2)
                        ->where('manual', 0)
                        ->where('crypto_currency_id', $currency->getKey())->get();

        foreach ($deposit_s as $deposit) {
            $usdt_col = Collection::firstWhere('deposit_id', $deposit->id);
            if ($usdt_col !== null) {
                $to_address = $deposit->address->address;

                if ($usdt_col->status === 0) {
                    $eth_txid = $cw->send_eth_fees($to_address);
                    if ($eth_txid === false) {
                        //Transaction Errored , retry in next run
                        $usdt_col->error = $cw->cwerror;
                        $usdt_col->save();
                    } else {
                        $usdt_col->txid = $eth_txid;
                        $usdt_col->status = 1;
                        $usdt_col->gas_price = substr($usdt_col->txid, 66);
                        $usdt_col->save();
                    }
                    //ADD TO DB
                } elseif ($usdt_col->status === 1) {
                    $eth_txid = $cw->send_usdt_wallet($to_address, $usdt_col->gas_price);
                    if ($eth_txid === false) {
                        //Transaction Errored , retry in next run
                        $usdt_col->error = $cw->cwerror;
                        $usdt_col->save();
                    } else {
                        $usdt_col->txid = $eth_txid;
                        $usdt_col->status = 2;
                        $usdt_col->save();
                    }

                } else {
                    //GET ANOTHER ACTIVE WALLET AND CHANGE STATUS
                }
            } else {
                //add in crypto_currencies_collection
                $usdt_col = new Collection();
                $usdt_col->crypto_currency_id = $currency->getKey();
                $usdt_col->status = 0;
                $usdt_col->deposit_id = $deposit->id;
                $usdt_col->save();
            }
        }
    }
}
