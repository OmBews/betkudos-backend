<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class User extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'email' => $this->email,
            'BTCBalance' => $this->btcBalance,
            'USDTBalance' => $this->usdtBalance,
            'eurBalance' => $this->eurBalance,
            'BTCProfitLoss' => $this->btcProfitLoss,
            'USDTProfitLoss' => $this->usdtProfitLoss,
            'BTCTotalStaked' => $this->btcTotalStaked,
            'USDTTotalStaked' => $this->usdtTotalStaked,
            'eurTotalStakedPrice' => $this->eurTotalStaked ? $this->eurTotalStaked : 0,
            'eurProfitLoss' => $this->profit_loss ?? $this->eurProfitLoss,
            'eurTotalStaked' => 'Pending',
            'ipAddress' => $this->ip_address,
            'status' => $this->status(),
            'restricted' => $this->restricted ? 'Restricted' : 'Unrestricted',
            'registeredAt' => $this->created_at,
            'self_x' => $this->self_x,
            'type' => $this->type,
            'btcPlaces' => count($this->wallets) > 0 ? $this->wallets[0]->currency->places : 8,
            'casinoTotalStaked' => $this->casino_total_staked ? $this->casino_total_staked : 0,
            'casinoTotalEurStaked' => $this->casino_eur_staked ? $this->casino_eur_staked : 0,
            'casinoTotalEarned' => $this->casino_total_earned ? $this->casino_total_earned : 0,
            'casinoProfitLoss' => $this->casino_profit_loss ? $this->casino_profit_loss : 0,
            'casinoEurProfitLoss' => $this->casino_eur_profit_loss ? $this->casino_eur_profit_loss : 0
        ];
    }
}
