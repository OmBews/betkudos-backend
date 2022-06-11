<?php

namespace App\Http\Controllers\Bost;

use App\Http\Controllers\Controller;
use App\Http\Resources\DepositResource;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Deposits\Deposit;
use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function filter(Request $request)
    {
        $request->validate([
            'currency' => 'nullable|string',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|in:20,50,200',
            'user_id' => 'nullable|integer'
        ]);

        $query = Deposit::query();

        if ($request->currency) {
            // $currency = $request->currency ? CryptoCurrency::ticker($request->currency)->first() : null;
            $query->where('crypto_currency_id', explode(',', $request->currency));
        }

        if ($request->status) {
            $query->where('status', explode(',', $request->status));
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $perPage = $request->per_page ?? 20;

        $deposits = $query->with($this->relations())->paginate($perPage, $this->columns());

        return DepositResource::collection($deposits);
    }

    private function relations(): array
    {
        return [
            'user:id,restricted,username',
            'currency',
            'address:id,address',
            'senderAddress'
        ];
    }

    private function columns(): array
    {
        return [
            'id', 'txid', 'status', 'amount', 'created_at',
            'user_id', 'crypto_currency_id', 'wallet_address_id'
        ];
    }
}
