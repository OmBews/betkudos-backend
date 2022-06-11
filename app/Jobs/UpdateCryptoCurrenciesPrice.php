<?php

namespace App\Jobs;

use App\Models\Currencies\CryptoCurrency;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class UpdateCryptoCurrenciesPrice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $eurQuotesResponse = $this->getCurrenciesFiatQuotes('EUR', [CryptoCurrency::TICKER_BTC, CryptoCurrency::TICKER_USDT]);
            $btcQuotesResponse = $this->getCurrenciesFiatQuotes('BTC', [CryptoCurrency::TICKER_BTC, CryptoCurrency::TICKER_USDT]);
            $usdQuotesResponse = $this->getCurrenciesFiatQuotes('USD', [CryptoCurrency::TICKER_BTC, CryptoCurrency::TICKER_USDT]);
            $gbpQuotesResponse = $this->getCurrenciesFiatQuotes('GBP', [CryptoCurrency::TICKER_BTC, CryptoCurrency::TICKER_USDT]);
            
            $btc = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first();
            $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first();
            // $play = CryptoCurrency::ticker(CryptoCurrency::TICKER_PLAY)->first();

            if ($btcQuotesResponse->json('data.BTC.quote.BTC.price')) {
                $btc->btc_price = $btcQuotesResponse->json('data.BTC.quote.BTC.price');
                $usdt->btc_price = $btcQuotesResponse->json('data.USDT.quote.BTC.price');
            }

            if ($eurQuotesResponse->json('data.BTC.quote.EUR.price')) {
                $btc->eur_price = $eurQuotesResponse->json('data.BTC.quote.EUR.price');
                $usdt->eur_price = $eurQuotesResponse->json('data.USDT.quote.EUR.price');
                // $play->eur_price = $eurQuotesResponse->json('data.USDT.quote.EUR.price');
            }

            if ($usdQuotesResponse->json('data.BTC.quote.USD.price')) {
                $btc->usd_price = $usdQuotesResponse->json('data.BTC.quote.USD.price');
                $usdt->usd_price = $usdQuotesResponse->json('data.USDT.quote.USD.price');
                // $play->usd_price = $usdQuotesResponse->json('data.USDT.quote.USD.price');
            }

            if ($gbpQuotesResponse->json('data.BTC.quote.GBP.price')) {
                $btc->gbp_price = $gbpQuotesResponse->json('data.BTC.quote.GBP.price');
                $usdt->gbp_price = $gbpQuotesResponse->json('data.USDT.quote.GBP.price');
                // $play->gbp_price = $gbpQuotesResponse->json('data.USDT.quote.GBP.price');
            }
            
            $btc->save();
            $usdt->save();
            // $play->save();
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    // key - @c18efcee-c687-4cd9-8429-fcd89fcf0751, 9edd1d17-67e6-455b-b8e9-0a81b270c9ff
    private function getCurrenciesFiatQuotes(string $fiat, array $symbols): \Illuminate\Http\Client\Response
    {
        return Http::get('https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest', [
            'symbol' => implode(',', $symbols),
            'convert' => $fiat,
            'CMC_PRO_API_KEY' => env('EXCHANGE_KEY', '9edd1d17-67e6-455b-b8e9-0a81b27w0c9ff')
        ]);
    }
}
