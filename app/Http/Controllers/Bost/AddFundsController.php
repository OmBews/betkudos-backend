<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Models\Deposits\Deposit;
use App\Models\Transactions\Transaction;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use App\Models\Wallets\WalletAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddFundsController extends Controller
{
    public function __construct()
    {
    }

    public function addFunds(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'amount' => 'required'
        ]);

        if ($request->amount > 1) {
            return 'You are not allowed to add more than 1 BTC';
        }

        DB::beginTransaction();

        try {
            $user = User::where('id', $request->id)->with('wallets', 'wallets.currency')->first();
            $btcWallet = $user->wallets->filter(fn ($usr) => $usr->currency->ticker == 'BTC');

            if (count($btcWallet) > 0) {

                // Add manual address in wallet addresses table like BTC address
                $address = new WalletAddress();
                $address->user_id = $user->id;
                $address->crypto_currency_id = $btcWallet[0]->crypto_currency_id;
                $address->wallet_id = $btcWallet[0]->id;
                $address->address = uniqid();
                $address->manual = 1;
                $address->save();

                // Add deposit
                $deposit = new Deposit();
                $deposit->user_id = $user->id;
                $deposit->wallet_id = $btcWallet[0]->id;
                $deposit->crypto_currency_id = $btcWallet[0]->crypto_currency_id;
                $deposit->amount = $request->amount;
                $deposit->status = 2;
                $deposit->wallet_address_id = $address->id;
                $deposit->txid = $address->address . time();
                $deposit->confirmations = 1;
                $deposit->manual = 1;
                $deposit->save();

                // Add Transactions
                if ($deposit) {

                    $wallet = Wallet::where('user_id', $user->id)->where('crypto_currency_id', $deposit->crypto_currency_id)->first();
                    $wallet->balance = $wallet->balance + $deposit->amount;
                    $wallet->save();

                    $transaction = new Transaction();
                    $transaction->user_id = $user->id;
                    $transaction->wallet_id = $deposit->wallet_id;
                    $transaction->transactionable_type = 'App\Models\Deposits\Deposit';
                    $transaction->transactionable_id = $deposit->id;
                    $transaction->manual = 1;
                    $transaction->save();
                }
            }

            DB::commit();

            return 'Funds added successfully';
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th;
        }
    }
}
