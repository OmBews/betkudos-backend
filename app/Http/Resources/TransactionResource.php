<?php

namespace App\Http\Resources;

use App\Models\Withdrawals\Withdrawal;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class TransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        if (App::environment('production')) {
            $btcPath = env('BTC_TXN_PATH', 'https://www.blockchain.com/btc/tx/');
        } else {
            $btcPath = env('BTC_TXN_TN_PATH', 'https://www.blockchain.com/btc-testnet/tx/');
        }

        return [
            'id' => $this->id,
            'networkId' => $this->transactionable->txid,
            'completed' => $this->transactionable->status === 2,
            'amount' => $this->transactionable->amount,
            'type' => $this->transactionable_type === Withdrawal::class ? 'withdraw' : 'deposit',
            'currency' => $this->when($this->transactionable->currency, fn() => $this->transactionable->currency),
            'date' => $this->created_at,
            'txnPath' => $btcPath,
            'manual' => $this->manual
        ];
    }
}
