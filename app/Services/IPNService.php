<?php

namespace App\Services;

use App\Blockchain\CryptoWallet;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Deposits\Deposit;
use App\Models\Transactions\Transaction;
use App\Models\Wallets\Wallet;
use App\Models\Wallets\WalletAddress;
use App\Models\Withdrawals\Withdrawal;

class IPNService
{
    public function process(string $txid)
    {
        $cw = new CryptoWallet();
        $currency = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first(['id']);

        $btc_tx = $cw->get_btc_transaction($txid);

        foreach ($btc_tx['details'] as $btc_vout) {
            if ($btc_vout['category'] == 'receive') {
                
                $wallet_address = WalletAddress::where('address', $btc_vout['address'])->where('crypto_currency_id', $currency->getKey())->first();
                if ($wallet_address !== null) {

                    $deposit = Deposit::where('txid', '=', $btc_tx['txid'])->first();

                    if (!$deposit) {
                        //New Deposit , not yet confirmed

                        $deposit = new Deposit();
                        $deposit->user_id = $wallet_address->user_id;
                        $deposit->wallet_id = $wallet_address->wallet_id;
                        $deposit->crypto_currency_id = $wallet_address->crypto_currency_id;
                        $deposit->amount = $btc_vout['amount'];
                        $deposit->wallet_address_id = $wallet_address->id;
                        $deposit->txid = $btc_tx['txid'];
                        $deposit->status = 0;
                        $deposit->confirmations = $btc_tx['confirmations'];

                        if ($btc_tx['confirmations'] > 0) {
                            $deposit->status = 2;
                            $deposit->confirmations = $btc_tx['confirmations'];
                            //Add user Balance
                            $wallet = Wallet::where('user_id', $deposit->user_id)->where('crypto_currency_id', $currency->getKey())->first();
                            $wallet->balance = $wallet->balance + $deposit->amount;
                            $wallet->save();
                        }

                        $deposit->save();
                    } else {
                        $deposit = Deposit::where('txid', $btc_tx['txid'])->first();

                        if ($btc_tx['confirmations'] > 0 && $deposit->status !== 2) {
                            $deposit->status = 2;
                            $deposit->confirmations = $btc_tx['confirmations'];
                            //add user balance
                            $wallet = Wallet::where('user_id', $deposit->user_id)->where('crypto_currency_id', $currency->getKey())->first();
                            $wallet->balance = $wallet->balance + $deposit->amount;
                            $wallet->save();
                        }

                        $deposit->save();
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
                }
            } else {
                // might be withdrawal

                $withdraw = Withdrawal::firstWhere('txid', $btc_tx['txid']);

                if ($withdraw !== null && $btc_tx['confirmations'] > 0 && $withdraw->status !== 2) {
                    $withdraw->status = 2;
                    $withdraw->confirmed_at = now()->toDateTimeString();
                    $withdraw->confirmations = $btc_tx['confirmations'];
                    $withdraw->save();
                }
            }
        }
    }
}
