<?php

namespace App\Http\Controllers\API;

use App\Contracts\Services\CasinoServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Resources\GameCategoryResource;
use App\Http\Resources\GameResource;
use App\Models\Casino\Games\Favourite;
use App\Models\Casino\Games\Game;
use App\Models\Casino\Games\GameCategory;
use App\Models\Casino\Providers\Provider;
use App\Services\CasinoService;
use App\Slotegrator\AggregatorRequest;
use App\Slotegrator\Slotegrator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Users\User;

class CasinoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->only('init');
        $this->middleware('slotegrator')->only('aggregator');
        $this->middleware('auth')->only(['updateCurrency', 'getGameProvider']);
    }

    /*
    |--------------------------------------------------------------------------
    | To show the list of casino games in Lobby or individual
    |--------------------------------------------------------------------------
    */
    public function games(Request $request, CasinoServiceInterface $service)
    {
        $request->validate([
            'search' => 'nullable|string',
            'category' => 'required_without:search|string',
            'providers' => 'nullable|string',
            'mobile' => 'nullable|boolean',
        ]);

        $userId = 0;
        if ($request->username) {
            $userId = $this->getUserId($request->username);
        }

        if ($request->category && $request->category === 'lobby') {
            if ($request->search) {
                return GameResource::collection($service->lobbysearch($request->search, $userId, !!$request->mobile));
            } else {
                return GameCategoryResource::collection($service->lobby($userId, !!$request->mobile));
            }
        }

        return GameResource::collection($service->filter($request->category, $request->providers, $request->search, $userId));
    }

    /*
    |--------------------------------------------------------------------------
    | To load a perticular game
    |--------------------------------------------------------------------------
    */
    public function game($user, Game $game)
    {
        $userId = $this->getUserId($user);
        $gameResponse = new GameResource($game);
        $checkFav = Favourite::where(['game_id' => $game->id, 'user_id' => $userId])->count();
        return response()->json(['data' => $gameResponse, 'fav' => $checkFav]);
    }

    public function providersHasGame()
    {
        $query = Provider::query();
        $query->whereHas('providerHasGame', function ($query) {
            $query->where('is_mobile', 0);
        });
        $query->where('status', 0);
        return $query->get(['id', 'name']);
    }

    public function providers()
    {
        return Provider::all(['id', 'name']);
    }

    public function getUserId($username)
    {
        $user = User::where('username', $username)->first();
        if ($user) {
            return $user->id;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Add a perticular game to favourite
    |--------------------------------------------------------------------------
    */
    public function addFavourite($user, Game $game)
    {
        try {
            $userId = $this->getUserId($user);
            if ($userId && $game) {

                $favourite = Favourite::where(['game_id' => $game->id, 'user_id' => $userId])->first();
                if (!$favourite) {
                    $fav = new Favourite();
                    $fav->user_id = $userId;
                    $fav->game_id = $game->id;
                    $fav->provider = $game->provider;
                    $fav->save();
                } else {
                    $favourite->delete();
                }
            }
            return 'Added to favourite';
        } catch (\Throwable $th) {
            return $th;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | To get game URL to play in real mode
    |--------------------------------------------------------------------------
    */
    public function init(Game $game, Request $request, CasinoServiceInterface $service)
    {
        $request->validate([
            'wallet' => 'required|integer|exists:wallets,id',
            'demo' => 'required|boolean',
            'cur' => 'required'
        ]);

        $user = $request->user();
        $wallet = $user->wallets()->where('id', $request->wallet)->first();

        if (!$wallet) {
            abort(404, 'Wallet not found!');
        }

        if (!$game) {
            abort(404, 'Game not found!');
        }

        $user->setWallet($wallet);

        return [
            'game' => $game,
            'redirect_url' => $service->init($game, $user, $request->wallet, $request->cur, (bool) $request->demo)
        ];
    }

    /**
     * Self - validate Slotegrator API
     */
    public function selfValidate(Request $request, CasinoServiceInterface $service)
    {
        return $service->selfValidate();
    }

    /*
    |--------------------------------------------------------------------------
    | Aggregator call for balance, Bet, Win, Refund and rollback
    |--------------------------------------------------------------------------
    */
    public function aggregator(AggregatorRequest $request, CasinoServiceInterface $service)
    {
        return match ($request->action) {

            "balance" => $service->balance($request),

            "bet" => $service->placeBet($request),

            "win" => $service->win($request),

            "refund" => $service->refund($request),

            "rollback" => $service->rollback($request),

            default => [
                'error_code' => 'INTERNAL_ERROR',
                'error_description' => 'Action not supported.'
            ]
        };
    }

    public function getCategory()
    {
        return GameCategory::where('name', '!=', 'Favourites')->get(['name']);
    }

    /*
    |--------------------------------------------------------------------------
    | To get exchange rate
    |--------------------------------------------------------------------------
    */
    public function conversion($ticker = 'BTC')
    {
        try {
            if ($ticker === "BTC") {
                $btc = CryptoCurrency::ticker(CryptoCurrency::TICKER_BTC)->first();
                return response()->json($btc);
            } else {
                $usdt = CryptoCurrency::ticker(CryptoCurrency::TICKER_USDT)->first();
                return response()->json($usdt);
            }
        } catch (\Throwable $th) {
            return $th;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Currency conversion and store in crypto currency model
    |--------------------------------------------------------------------------
    */
    public function currencyConversion()
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
            'CMC_PRO_API_KEY' => '9edd1d17-67e6-455b-b8e9-0a81b270c9ff'
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Add Currency in provider list
    |--------------------------------------------------------------------------
    */
    public function updateCurrency($flag)
    {

        if ($flag !== 'hbit') {
            return 'You are not authorised person';
        }

        $providerList = [
            [
                "name" => "Amatic",
                "currency" => 'EUR'
            ],
            [
                "name" => "Belatra Games",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "Betgames",
                "currency" => 'BTC,EUR'
            ],
            [
                "name" => "Betradar",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Betsolutions",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "BoomingGames",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Booongo",
                "currency" => 'EUR'
            ],
            [
                "name" => "Caleta",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "Charismatic",
                "currency" => 'EUR'
            ],
            [
                "name" => "Dlv",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "EGT",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Elbet",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "ELK",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Endorphina",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "EurasianGamingBingo",
                "currency" => 'EUR'
            ],
            [
                "name" => "Evolution2",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Evoplay",
                "currency" => 'EUR'
            ],
            [
                "name" => "GameArt",
                "currency" => 'EUR'
            ],
            [
                "name" => "Gamshy",
                "currency" => 'EUR'
            ],
            [
                "name" => "Green Jade",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Habanero",
                "currency" => 'EUR'
            ],
            [
                "name" => "Igrosoft",
                "currency" => 'EUR'
            ],
            [
                "name" => "KAGaming",
                "currency" => 'BTC,EUR'
            ],
            [
                "name" => "LeapGaming",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "Lotto Instant Win",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "MacawGaming",
                "currency" => 'USD'
            ],
            [
                "name" => "MascotGaming",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Microgaming",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Netent Standard",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "NetGame",
                "currency" => 'EUR'
            ],
            [
                "name" => "NetGaming",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "No Limit City",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "OneTouch",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Playson",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "PragmaticPlay",
                "currency" => 'BTC,EUR'
            ],
            [
                "name" => "Pragmatic Play",
                "currency" => 'BTC,EUR'
            ],
            [
                "name" => "Push Gaming",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Quickspin",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Red Tiger Slot",
                "currency" => 'EUR'
            ],
            [
                "name" => "RedRake",
                "currency" => 'BTC,EUR'
            ],
            [
                "name" => "ReelNRG",
                "currency" => 'EUR'
            ],
            [
                "name" => "Relax Gaming Slots",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Relax Gaming Table",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "RetroGaming",
                "currency" => 'EUR'
            ],
            [
                "name" => "RevolverGaming",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "RTG SLOTS",
                "currency" => 'EUR'
            ],
            [
                "name" => "SkyWind",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Spadegaming",
                "currency" => 'USD'
            ],
            [
                "name" => "SpiffbetGames",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Spinmatic",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "Spinomenal",
                "currency" => 'BTC,EUR'
            ],
            [
                "name" => "SuperSpadeGames",
                "currency" => 'EUR'
            ],
            [
                "name" => "Thunderkick",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Tomhorn",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "TripleCherry",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "Truelab",
                "currency" => 'EUR'
            ],
            [
                "name" => "Vivogaming",
                "currency" => 'BTC,USD,EUR'
            ],
            [
                "name" => "Wazdan",
                "currency" => 'BTC,EUR'
            ],
            [
                "name" => "XProgaming",
                "currency" => 'EUR'
            ],
            [
                "name" => "Yggdrasil",
                "currency" => 'USD,EUR'
            ],
            [
                "name" => "HollywoodTV",
                "currency" => 'EUR'
            ],
            [
                "name" => "GoldenRace",
                "currency" => 'EUR'
            ]
        ];

        try {
            foreach ($providerList as $pro) {
                $name = $pro['name'];
                $currency = $pro['currency'];

                if ($currency) {
                    // Update in provider model
                    $provider = Provider::where('name', $name)->first();
                    if ($provider) {
                        $provider->currency = $currency;
                        $provider->save();

                        // Update supported currency in game model 
                        $this->updateGameCurrency($name, $currency);
                    }
                }
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return 1;
    }

    public function updateGameCurrency($provider, $currency)
    {
        $game = Game::where('provider', $provider)->first();
        if ($game) {
            $game->has_currency = $currency;
            $game->save();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | extrach game and their provider list
    |--------------------------------------------------------------------------
    */
    public function getGameProvider($key, Game $game)
    {
        if ($key !== 'hbit') {
            return 'You are not authorised person';
        }

        $result = $game->where(['is_active' => true])
            ->orderBy('provider')->get(['provider', 'name', 'category']);
        echo '<table>';
        foreach ($result as $res) {
            echo "<tr><td>" . $res->provider . "</td><td>" . $res->name . "</td><td>" . $res->category . "</td></tr>";
        }
        echo '</table>';
    }
}
