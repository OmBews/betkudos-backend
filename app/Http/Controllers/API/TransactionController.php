<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\TransactionResource;
use App\Models\Transactions\Transaction;
use App\Models\Users\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:bookie')->only('byUser');
    }

    public function index(Request $request)
    {
        $request->validate([
            'walletId' => 'required|integer|exists:wallets,id'
        ]);

        $user = $request->user();

        $transactions = Transaction::query()
            ->where('user_id', $user->getKey())
            ->where('wallet_id', $request->walletId)
            ->with(['transactionable', 'transactionable.currency'])
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return TransactionResource::collection($transactions);
    }

    public function byUser(User $user)
    {
        $transactions = Transaction::query()
            ->where('user_id', $user->getKey())
            ->with(['transactionable', 'transactionable.currency'])
            ->orderBy('id', 'DESC')
            ->paginate(10);

        return TransactionResource::collection($transactions);
    }
}
