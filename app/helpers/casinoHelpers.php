<?php
use App\Models\Casino\Games\GameSession;
use App\Models\Wallets\Wallet;
use Illuminate\Support\Facades\Log;

if(!function_exists('deductBalanceFromWallet'))
{
    function deductBalanceFromWallet($amount, $sessionKey)
    {
        return true;
    }
}