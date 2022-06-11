<?php

namespace App\Models\Transactions;

use App\Models\Deposits\Deposit;
use App\Models\Withdrawals\Withdrawal;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    public function transactionable()
    {
        return $this->morphTo();
    }
}
