<?php

namespace Database\Seeders;

use App\Models\Currencies\CryptoCurrency;
use Illuminate\Database\Seeder;

class CryptoCurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->currencies() as $currency) {
            CryptoCurrency::query()->updateOrCreate(['ticker' => $currency['ticker']], $currency);
        }
    }

    public function currencies(): array
    {
        return [
            [
                'name' => 'Bitcoin',
                'ticker' => 'BTC',
                'icon' => 'assets/img/currencies/btc-logo.png',
                'min_bet' => 0.00001,
                'max_bet' => 0.05,
                'max_bet_profit' => 0.3,
                'places' => 8,
                'network_fee' => 0.0002
            ],
            [
                'name' => 'Tether',
                'ticker' => 'USDT',
                'icon' => 'assets/img/currencies/play-logo.png',
                'min_bet' => 0.1,
                'max_bet' => 1500,
                'max_bet_profit' => 10000,
                'places' => 2
            ]
        ];
    }
}
