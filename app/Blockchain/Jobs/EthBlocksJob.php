<?php

namespace App\Blockchain\Jobs;

use App\Models\BlockTrack;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Deposits\Deposit;
use App\Models\Transactions\Transaction;
use App\Models\Wallets\Wallet;
use App\Models\Wallets\WalletAddress;
use App\Models\Withdrawals\Withdrawal;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Blockchain\CryptoWallet;

class EthBlocksJob implements ShouldQueue
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

        $cron_hash = $cw->generateRandomString(20);

        // check if cron_hash is null in DB

        $block_track = BlockTrack::firstOrCreate(['crypto_currency_id' => $currency->getKey()]);

        if ($block_track->cron_hash === null) {
            $block_track->cron_hash = $cron_hash;
            $block_track->save();

            // check again
            $block_track = BlockTrack::firstWhere('crypto_currency_id', $currency->getKey());

            if ($block_track->block_number === null) {
                $eth_block_num = $cw->get_current_block('eth');
                $block_track->block_number = $eth_block_num;
                $block_track->save();
            }

            if ($block_track->cron_hash === $cron_hash) {
                $eth_block_num = $cw->get_current_block('eth');

                //TODO: CHECK IN DB AND UPDATE IN DB
                while ($eth_block_num > $block_track->block_number) {
                    $eth_logs = $cw->eth_get_topics($block_track->block_number + 1);
                    foreach ($eth_logs as $eth_tx) {
                        /**echo 'To:'.'0x'.substr($eth_tx['topics'][2],26);
                         * echo "\r\n";
                         * echo 'Amount:'.hexdec($eth_tx['data'])/pow(10,6);
                         * echo "\r\n";
                         * echo 'Txid:'.$eth_tx['transactionHash'];
                         * echo "\r\n";*/

                        //TODO : CHECK IN DB AND ADD TO DB

                        $wallet_address = WalletAddress::where('address', '=', '0x' . substr($eth_tx['topics'][2], 26), 'and')->where('crypto_currency_id', '=', $currency->getKey())->first();
                        $deposit = Deposit::firstWhere('txid', $eth_tx['transactionHash']);

                        if ($wallet_address !== null && $deposit === null) {
                            //found address credit the Deposit
                            $deposit = new Deposit();
                            $deposit->user_id = $wallet_address->user_id;
                            $deposit->wallet_id = $wallet_address->wallet_id;
                            $deposit->crypto_currency_id = $wallet_address->crypto_currency_id;
                            $deposit->amount = hexdec($eth_tx['data']) / pow(10, 6);
                            $deposit->wallet_address_id = $wallet_address->id;
                            $deposit->txid = $eth_tx['transactionHash'];
                            $deposit->status = 1;
                            $deposit->confirmations = 0;
                            $deposit->save();

                            $transaction = new Transaction();
                            $transaction->user_id = $wallet_address->user_id;
                            $transaction->wallet_id = $wallet_address->wallet_id;
                            $transaction->transactionable_type = Deposit::class;
                            $transaction->transactionable_id = $deposit->getKey();
                            $transaction->save();
                        }
                    }

                    //check older TX for confirmations

                    $eth_logs = $cw->eth_get_topics($block_track->block_number - 10);
                    foreach ($eth_logs as $eth_tx) {
                        /**echo 'To:'.'0x'.substr($eth_tx['topics'][2],26);
                         * echo "\r\n";
                         * echo 'Amount:'.hexdec($eth_tx['data'])/pow(10,6);
                         * echo "\r\n";
                         * echo 'Txid:'.$eth_tx['transactionHash'];
                         * echo "\r\n";*/

                        //TODO : CHECK IN DB AND ADD TO DB

                        $wallet_address = WalletAddress::where('address', '0x' . substr($eth_tx['topics'][2], 26))
                            ->where('crypto_currency_id', '=', $currency->getKey())
                            ->first();

                        if ($wallet_address !== null) {
                            //found address add up the Confs the Deposit
                            $deposit = Deposit::where('txid', $eth_tx['transactionHash'])->first();
                            if ($deposit === null) {
                                $deposit = new Deposit();
                                $deposit->user_id = $wallet_address->user_id;
                                $deposit->crypto_currency_id = $wallet_address->crypto_currency_id;
                                $deposit->wallet_id = $wallet_address->wallet_id;
                                $deposit->amount = hexdec($eth_tx['data']) / pow(10, 6);
                                $deposit->wallet_address_id = $wallet_address->id;
                                $deposit->txid = $eth_tx['transactionHash'];
                                $deposit->status = 2;
                                $deposit->confirmations = 10;
                                $deposit->save();

                                //Add balance to user
                                $wallet = Wallet::where('user_id', $wallet_address->user_id)->where('crypto_currency_id', $currency->getKey())->first();
                                $wallet->balance = $wallet->balance + $deposit->amount;
                                $wallet->save();
                            } else {
                                if ($deposit->status < 2) {
                                    $deposit->status = 2;
                                    $deposit->confirmations = 10;
                                    $deposit->save();

                                    $wallet = Wallet::where('user_id', $wallet_address->user_id)->where('crypto_currency_id', $currency->getKey())->first();
                                    $wallet->balance = $wallet->balance + $deposit->amount;
                                    $wallet->save();
                                }
                            }

                            $transaction = Transaction::firstWhere('transactionable_id', $deposit->getKey());

                            if (!$transaction) {
                                $transaction = new Transaction();
                                $transaction->user_id = $wallet_address->user_id;
                                $transaction->wallet_id = $wallet_address->wallet_id;
                                $transaction->transactionable_type = Deposit::class;
                                $transaction->transactionable_id = $deposit->getKey();
                                $transaction->save();
                            }
                        } else {
                            //address not in wallet so check withdrawal
                            $withdraw = Withdrawal::where('txid', $eth_tx['transactionHash'])->first();
                            if ($withdraw !== null) {
                                $withdraw->confirmed_at = now()->toDateTimeString();
                                $withdraw->status = 2;
                                $withdraw->confirmations = 10;
                                $withdraw->save();
                            }
                        }
                    }

                    $block_track->block_number = $block_track->block_number + 1;
                    $block_track->save();
                }

                $block_track->cron_hash = null;
                $block_track->save();
            }
        } else {
            // Skip as another Cron Running
        }
    }
}
