<?php

namespace App\Http\Controllers\API;

use App\Blockchain\CryptoWallet;
use App\Http\Controllers\Controller;
use App\Http\Resources\WithdrawResource;
use App\Models\Bets\Bet;
use App\Models\Casino\Games\CasinoBet;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Deposits\Deposit;
use App\Models\kyc\UserKyc;
use App\Models\RiskMonitor;
use App\Models\Transactions\Transaction;
use App\Models\Wallets\Wallet;
use App\Models\Withdrawals\Withdrawal;
use Google\Service\PolyService\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use phpDocumentor\Reflection\Types\Nullable;

class WithdrawalController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('role:bookie')->only('filter');
    }

    public function create(Request $request)
    {
        $request->validate([
            'walletId' => 'required|integer|exists:wallets,id',
            'address' => 'required|string|max:255',
            'amount' => 'required|numeric',
        ]);

        $user = $request->user();
        $address = $request->address;
        $amount = $request->amount;

        $wallet = $user->wallets()->with('currency')->findOrFail($request->walletId);
        $ticker = strtolower($wallet->currency->ticker); // Ticker - Currency code

        // Check withdrawal if any exist
        $isWithdrawal = Withdrawal::where('address', $address)->first();
        if ($isWithdrawal) {
            abort(400, "Can't process withdrawal because the address is linked to another account");
        }

        // Check wallet balance if it is low to withdrawal amout
        if ($wallet->balance < $amount + $wallet->currency->network_fee) {
            abort(400, "The amount exceeds the available balance + transaction fees.");
        }

        $cw = new CryptoWallet();
        
        /**
         * To get BTC transactions
         * $btc_tx = $cw->get_btc_transaction($address); 
         * return $btc_tx;
         */ 
        
        $balance = $cw->get_balance($ticker); // Get Hot Balance

        // Check if Hot BTC Balance is low
        if ($balance < $amount + $wallet->currency->network_fee) {
            abort(400, "Withdrawals will be available shortly, Please try again soon.");
        }

        // Check deposit, atleast should be one deposit else withdrawal will pending
        $deposite = Deposit::where('user_id', $user->id)->first();

        // Check pending withdrawal if any
        $checkPendingWd = $this->checkPendingWithdrawal($deposite, $user->id);
        if ($checkPendingWd > 0) {
            abort(400, "Withdrawal pending information - you should receive an email from support@betkudos.com shortly");
        }

        // Check users bet settled atleast one
        $checkSetteledAmt = $this->checkSettledBet($user->id, $request->walletId, $wallet->currency->ticker);
        if ($checkSetteledAmt < $amount) {
            abort(400, "Initial deposit must be wagered in order to deposit.");
        }

        $usdAmount = ($wallet->currency->usd_price * $amount); // convert withdrawal amount in USD from USDT or BTC
        
        // Verify KYC Level one
        $kyc = UserKyc::where('user_id', $user->id)->first();
        if (!$kyc) {
            abort(400, "KYC information outstanding.");
        }

        if ($kyc->kyc_status === 0) {
            abort(400, "KYC information outstanding.");
        }

        // Force fully update KYC Level Three verifying if amount GT 5000
        if ($usdAmount >= 5) {
            $levelThreeStatus = $this->forceVarifyingLevelThree($kyc->id);
            if ($levelThreeStatus == 0) {
                abort(400, "KYC level three information outstanding.");    
            }
        }

        // Force fully update KYC Level two verifying
        if ($usdAmount >= Withdrawal::KYC_LIMIT) {
            $levelTwoStatus = $this->forceVarifyingLevelTwo($kyc->id);
            if ($levelTwoStatus == 0) {
                abort(400, "KYC level two information outstanding.");   
            }
        }

        // Check Daily limit of 10000
        $checkDailyLimit = $this->checkDailyWdLimit($user->id, $wallet);
        if ($checkDailyLimit > 10) {
            abort(400, "Exceed Maximum Withdrawal Amount.");
        }

        DB::beginTransaction();

        try {

            $txid = $cw->create_withdrawal($ticker, $address, $amount);

            $withdraw = new Withdrawal();
            $withdraw->user_id = $user->getKey();
            $withdraw->crypto_currency_id = $wallet->currency->getKey();
            $withdraw->wallet_id = $wallet->getKey();
            $withdraw->txid  = $txid ?? '';

            if ($usdAmount > Withdrawal::KYC_LIMIT) {
                $withdraw->status = 0;
            } elseif (!$deposite) {
                $withdraw->status = 0;
            } else {
                $withdraw->status = $txid ? 1 : 0;
            }

            $withdraw->confirmations = 0;
            $withdraw->amount = $amount;
            $withdraw->error = $txid ? null : $cw->cwerror;
            $withdraw->address  = $address;
            $withdraw->fee  = $wallet->currency->network_fee;
            $withdraw->save();

            $transaction = new Transaction();
            $transaction->wallet_id = $wallet->getKey();
            $transaction->user_id = $user->getKey();
            $transaction->transactionable_type = Withdrawal::class;
            $transaction->transactionable_id = $withdraw->getKey();

            $wallet->balance -= $amount;

            $wallet->save();
            $transaction->save();

            DB::commit();

            return ['message' => "Your withdraw for $amount {$wallet->currency->ticker} is being processed."];
        } catch (\Exception $exception) {
            DB::rollBack();

            throw $exception;
        }
    }

    public function checkPendingWithdrawal($deposite, $user)
    {
        $chkWd = Withdrawal::where('user_id', $user)->where('status', 0)->first();
        if ($chkWd) {
            return 1;
        } else {
            return 0;
        }
    }

    public function checkSettledBet($user, $wallet, $currency)
    {
        $queryBet = Bet::where('user_id', $user)->where('status', '!=', Bet::STATUS_OPEN)->where('wallet_id', $wallet)->get();
        $betStake = $queryBet->sum(fn ($bet) => $bet->stake);

        $queryCasino = CasinoBet::where('type', 'bet')->where('player_id', $user)->where('crypto_currency', $currency)->get();
        $casinoStake = $queryCasino->sum(fn ($casino) => $casino->crypto_amt);

        return $betStake + $casinoStake;
    }

    public function checkDailyWdLimit($user, $wallet)
    {
        $dailyWd =  Withdrawal::where('user_id', $user)
            ->where('crypto_currency_id', $wallet->currency->getKey())
            ->where('created_at', '>=', now()->startOfDay()->toDateTimeString())
            ->where('created_at', '<=', now()->endOfDay()->toDateTimeString())->get();
        $amount = $dailyWd->sum(fn ($wd) => $wd->amount);
        return number_format(($amount * $wallet->currency->usd_price), 2);
    }

    public function forceVarifyingLevelTwo($id)
    {
        $kycUser = UserKyc::find($id);
        
        if ($kycUser->kyc_status_two == 0) {
            return 0;
        }

        // $kycUser->kyc_status_two = 0; // Use to forcefully update status to verifying
        // $kycUser->save;
        return 1;
    }

    public function forceVarifyingLevelThree($id)
    {
        $kycUser = UserKyc::find($id);

        if ($kycUser->kyc_status_three == 0 || $kycUser->kyc_status_two == 0) {
            return 0;
        }

        // $kycUser->kyc_status_three = 0; // Use to forcefully update status to verifying
        // $kycUser->save;
        return 1;
    }

    public function filter(Request $request)
    {
        $request->validate([
            'currency' => 'nullable|string',
            'status' => 'nullable|string',
            'per_page' => 'nullable|integer|in:20,50,200',
            'user_id' => 'nullable|integer'
        ]);

        $query = Withdrawal::query();

        if ($request->currency) {
            // $currency = $request->currency ? CryptoCurrency::ticker($request->currency)->first() : null;
            $currency = explode(',', $request->currency);
            $query->whereIn('crypto_currency_id', $currency);
        }

        if ($request->status) {
            $query->whereIn('status', explode(',', $request->status));
        }

        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $perPage = $request->per_page ?? 20;
        $withdrawals = $query->with($this->relations())->paginate($perPage, $this->columns());
        return WithdrawResource::collection($withdrawals);
    }

    private function relations(): array
    {
        return [
            'user:id,restricted,username',
            'currency',
            'userKyc'
        ];
    }

    private function columns(): array
    {
        return [
            'id', 'txid', 'status',
            'fee', 'amount', 'created_at',
            'user_id', 'crypto_currency_id',
            'confirmed_at', 'address'
        ];
    }

    public function approveWithdrawal(Request $request)
    {
        $request->validate([
            'wid' => 'nullable|integer'
        ]);

        try {
            $withdraw = Withdrawal::find($request->wid);
            if ($withdraw) {
                $withdraw->status = 1;
                $withdraw->save();
            }
            return 'Approved Withdrawals';
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    public function getBtcBalance()
    {
        try {
            $cw = new CryptoWallet;
            $balance = $cw->get_balance('btc');

            if ($balance) {
                $riskMonitor = RiskMonitor::WhereDate('created_at', date('Y-m-d'))->first();

                if (!$riskMonitor) {
                    $riskMonitor = new RiskMonitor();
                    $riskMonitor->amount = $balance;
                    $riskMonitor->attempts = 1;
                    $riskMonitor->save();
                }

                return [
                    'initial_balance' => $riskMonitor->amount,
                    'current_balance' => $balance
                ];
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
