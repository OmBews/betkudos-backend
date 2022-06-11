<?php

namespace App\Http\Controllers\API;

use App\Blockchain\CryptoWallet;
use App\Http\Controllers\Controller;
use App\Http\Resources\WalletResource;
use App\Models\Wallets\Wallet;
use App\Models\Wallets\WalletAddress;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function generateHotAddress(Request $request) {
        $cw = new CryptoWallet();

        $address = $cw->create_address(strtolower('btc'));

        if (!$address) {
            abort(500, 'Got a error from node'); // Got a error from node
        }
        return $address;
    }

    public function generateAddress(Wallet $wallet, Request $request)
    {
        $user = $request->user();

        if ($wallet->user_id !== $user->id) 
        {
            abort(403);
        }

        $wallet->load(['currency', 'address']);

        if (!$wallet->address || $wallet->address->created_at->lessThanOrEqualTo(now()->subMinutes(30))) {
            $cw = new CryptoWallet();

            $address = $cw->create_address(strtolower($wallet->currency->ticker));

            if (!$address) {
                abort(500, 'Got a error from node'); // Got a error from node
            }

            $walletAddress = new WalletAddress();

            $walletAddress->wallet_id = $wallet->getKey();
            $walletAddress->crypto_currency_id = $wallet->currency->getKey();
            $walletAddress->user_id = $user->getKey();
            $walletAddress->address = $address;

            $walletAddress->save();

            $wallet->setRelation('address', $walletAddress);
        } else {
            abort(400, 'You can generate only 1 address every 30 minutes.');
        }

        return new WalletResource($wallet);
    }
}
