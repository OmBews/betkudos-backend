<?php

namespace Tests\Feature\Http\Controllers\API\Bets;

use App\Exceptions\Betting\AsianHandicapOnMultipleException;
use App\Exceptions\Betting\BetExpiredException;
use App\Exceptions\Betting\DelayedBetSentTooEarlyException;
use App\Exceptions\Betting\OddsHasChangedException;
use App\Exceptions\Betting\ProfitOverLimitException;
use App\Exceptions\Betting\SelectionSuspendedException;
use App\Exceptions\Betting\UnavailableSelectionException;
use App\Exceptions\Betting\WrongBetHashException;
use App\Jobs\Matches\ProcessLiveOdds;
use App\Jobs\Matches\ProcessResults;
use App\Models\Bets\Bet;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Event;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use App\Services\BettingService;
use Database\Seeders\CryptoCurrenciesTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Testing\TestResponse;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Database\Seeders\SettingsTableSeeder;
use Database\Seeders\MarketGroupsTableSeeder;
use Database\Seeders\SoccerMarketsSeeder;

class PlaceBetTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SettingsTableSeeder::class);
        $this->seed(MarketGroupsTableSeeder::class);
        $this->seed(SoccerMarketsSeeder::class);
        $this->seed(CryptoCurrenciesTableSeeder::class);
    }

    protected function placeBetRoute()
    {
        return route('bets.place-bet');
    }

    protected function single(array $overrides = []): array
    {
        $default = [
            'stake' => 1,
            'match_id' => rand(),
            'odd_id' => rand(),
            'market_id' => rand(),
            'odds' => 1.20,
        ];

        return array_merge($default, $overrides);
    }

    protected function genSelection(array $overrides = [])
    {
        $defaults = [
            'id' => rand(1, 1000),
            'name' => $name = ['1', 'X', '2'][rand(0, 2)],
            'odds' => $this->genRandomOdds(),
            'header' => $name,
            'handicap' => null,
        ];

        return array_merge($defaults, $overrides);
    }

    protected function genRandomOdds(): float
    {
        return round((mt_rand(1, 100) / 100) + 1, 2);
    }

    protected function createMatchesForPlaceBet(int $amount = 1, array $matchAttrs = [], array $oddsAttrs = [], int $marketId = 1)
    {
        $defaultMatchAttrs = [];

        $matches = factory(Event::class, $amount)->create(array_merge($defaultMatchAttrs, $matchAttrs));

        $matches->each(function (Event $match) use($marketId, $oddsAttrs) {
            $this->createMatchOdds($match, $marketId, $oddsAttrs);
        });

        return $matches;
    }

    protected function createMatchOdds(Event $match, int $marketId = 1, array $overrideAttrs = [])
    {
        $defaultOddsAttrs = $this->genSelection([
            'market_id' => $marketId,
            'is_live' => $match->isLive()
        ]);

        return $match->odds()->save(
            factory(MarketOdd::class)->make(
                array_merge($defaultOddsAttrs, $overrideAttrs)
            )
        );
    }

    protected function handleLiveBettingDelay(TestResponse $response, array $payload, int $wait = null, string $hash = null): TestResponse
    {
        $data = $response->json('data');

        if ($data && array_key_exists('betUniqueId', $data)) {
            $payload = array_merge($payload, ['betUniqueId' => $hash ?? $data['betUniqueId']]);

            $this->travel($wait ?? $data['wait'])->milliseconds();

            return $this->postJson($this->placeBetRoute(), $payload);
        }

        return $response;
    }

    protected function createUserWallets(User $user, $amount = 1, array $data = []): \Illuminate\Support\Collection
    {
        $wallets = [];

        foreach (CryptoCurrency::all()->take($amount) as $currency) {
            $wallets[] = Wallet::factory()->create(array_merge(['user_id' => $user->getKey(), 'crypto_currency_id' => $currency->getKey()], $data));
        }

        return collect($wallets);
    }

    public function matchStatusDataProvider(): array
    {
        return [
          [Event::STATUS_IN_PLAY],
          [Event::STATUS_NOT_STARTED],
        ];
    }

    /**
     * @dataProvider matchStatusDataProvider
     */
    public function testUserCanPlaceMultipleBets($status)
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();

        $previouslyBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet($eventsCount = 3, [
            'time_status' => $live = $status
        ]);
        $selections = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $selections[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'name' => $matchOdds->name,
                'header' => $matchOdds->header,
                'handicap' => $matchOdds->handicap,
                'market_id' => 1, // Full Time result
                'stake' => null,
            ]);
        }

        Passport::actingAs($user);

        $payload = [
            'multiples' => $selections,
            'multipleStake' => $stake = $wallet->currency->min_bet,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);
        $response->assertSuccessful();

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload);
        $delayedBetResponse->assertSuccessful();
        $delayedBetResponse->assertJsonPath('data.0.wallet.id', $wallet->getKey());

        $user->refresh();
        $wallet->refresh();

        $bet = Bet::query()->where('user_id', $user->getKey())->first();

        $this->assertEquals($previouslyBalance - $stake, $wallet->balance);
        $this->assertDatabaseHas('bets', [
            'id' => $bet->getKey(),
            'user_id' => $user->getKey(),
            'type' => Bet::TYPE_MULTIPLE,
            'stake' => $stake,
            'live' => $live,
            'status' => Bet::STATUS_OPEN,
            'wallet_id' => $wallet->getKey(),
        ]);

        foreach ($selections as $payload) {
            $this->assertDatabaseHas('bet_selections', [
                'bet_id' => $bet->getKey(),
                'match_id' => $payload['match_id'],
                'market_id' => $payload['market_id'],
                'odd_id' => $payload['odd_id'],
                'odds' => $payload['odds'],
                'name' => $payload['name'],
                'handicap' => $payload['handicap'],
                'header' => $payload['header'],
            ]);
        }

        if ($status === Event::STATUS_IN_PLAY) {
            Bus::assertDispatchedAfterResponseTimes(ProcessLiveOdds::class, $eventsCount);
            Bus::assertDispatchedAfterResponseTimes(ProcessResults::class, $eventsCount);
        }
    }

    /**
     * @dataProvider matchStatusDataProvider
     */
    public function testUserCanPlaceSingleBets($status)
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $previouslyBalance = $wallet->balance;
        $singleStake = $wallet->currency->min_bet;
        $amount = 10;
        $matches = $this->createMatchesForPlaceBet($amount, [
            'time_status' => $live = $status
        ]);
        $singles = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $singles[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'name' => $matchOdds->name,
                'header' => $matchOdds->header,
                'handicap' => $matchOdds->handicap,
                'market_id' => 1, // Full Time result
                'stake' => $singleStake,
            ]);
        }

        Passport::actingAs($user);

        $payload =  [
            'singles' => $singles,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);

        $response->assertSuccessful();

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload);

        $delayedBetResponse->assertSuccessful();
        $delayedBetResponse->assertJsonCount($amount, 'data');

        $user->refresh();
        $wallet->refresh();

        $bets = Bet::query()->where('user_id', $user->getKey())->with('selections')->get();

        $this->assertTrue($bets->count() > 0);
        // Making sure that user balance should be the
        // initial value less the single stake * $amount of matches
        $this->assertEquals($previouslyBalance - ($singleStake * $amount), $wallet->balance);
        foreach ($bets as $bet) {
            $this->assertDatabaseHas('bets', [
                'id' => $bet->getKey(),
                'user_id' => $user->getKey(),
                'type' => Bet::TYPE_SINGLE,
                'stake' => $singleStake,
                'live' => $live,
                'status' => Bet::STATUS_OPEN,
                'wallet_id' => $wallet->getKey(),
            ]);
            $this->assertTrue(count($bet->selections) === 1);
        }

        foreach ($singles as $payload) {
            $this->assertDatabaseHas('bet_selections', [
                'match_id' => $payload['match_id'],
                'market_id' => $payload['market_id'],
                'odd_id' => $payload['odd_id'],
                'odds' => $payload['odds'],
                'name' => $payload['name'],
                'handicap' => $payload['handicap'],
                'header' => $payload['header'],
            ]);
        }

        if ($status === Event::STATUS_IN_PLAY) {
            Bus::assertDispatchedAfterResponseTimes(ProcessLiveOdds::class, $amount);
            Bus::assertDispatchedAfterResponseTimes(ProcessResults::class, $amount);
        }
    }

    /**
     * @dataProvider matchStatusDataProvider
     */
    public function testUserCanPlaceMultipleAndSingleBetsAtTheSameRequest($status)
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $previousBalance = $wallet->balance;
        $singleStake = $wallet->currency->min_bet;
        $multipleStake = $wallet->currency->min_bet;
        $amount = 4;
        // The number of singles plus the multiple bet
        $totalOfBets = $amount + 1;
        $matches = $this->createMatchesForPlaceBet($amount, [
            'time_status' => $live = $status
        ]);
        $singlesPayload = [];
        $multiplesPayload = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $single = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'name' => $matchOdds->name,
                'header' => $matchOdds->header,
                'handicap' => $matchOdds->handicap,
                'market_id' => 1, // Full Time result
                'stake' => $singleStake
            ]);
            $singlesPayload[] = $single;
            $multiplesPayload[] = Arr::except($single, ['stake']);
        }

        Passport::actingAs($user);

        $payload = [
            'multiples' => $multiplesPayload,
            'singles' => $singlesPayload,
            'multipleStake' => $multipleStake,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);

        $response->assertSuccessful();

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload);

        $delayedBetResponse->assertSuccessful();
        $delayedBetResponse->assertJsonCount($totalOfBets, 'data');

        $user->refresh();
        $wallet->refresh();

        $bets = Bet::query()->where('user_id', $user->getKey())->get();

        $multiple = Bet::query()
            ->where('user_id', $user->getKey())
            ->where('type', Bet::TYPE_MULTIPLE)
            ->with('selections')
            ->get();

        $singles = Bet::query()
            ->where('user_id', $user->getKey())
            ->where('type', Bet::TYPE_SINGLE)
            ->with('selections')
            ->get();

        $this->assertTrue($bets->count() === $totalOfBets);
        $this->assertTrue($multiple->count() === 1);
        $this->assertTrue($singles->count() === $amount);
        $newBalance = $previousBalance - (($amount * $singleStake) + $multipleStake);
        $this->assertEquals($newBalance, $wallet->balance);

        foreach ($singles as $single) {
            $this->assertDatabaseHas('bets', [
                'id' => $single->getKey(),
                'user_id' => $user->getKey(),
                'type' => Bet::TYPE_SINGLE,
                'stake' => $singleStake,
                'live' => $live,
                'status' => Bet::STATUS_OPEN,
                'wallet_id' => $wallet->getKey(),
            ]);
            $this->assertTrue(count($single->selections) === 1);
        }

        foreach ($multiple as $bet) {
            $this->assertDatabaseHas('bets', [
                'id' => $bet->getKey(),
                'user_id' => $user->getKey(),
                'type' => Bet::TYPE_MULTIPLE,
                'stake' => $multipleStake,
                'live' => $live,
                'status' => Bet::STATUS_OPEN,
                'wallet_id' => $wallet->getKey(),
            ]);
            $this->assertTrue(count($bet->selections) === $amount);
        }

        if ($status === Event::STATUS_IN_PLAY) {
            Bus::assertDispatchedAfterResponseTimes(ProcessLiveOdds::class, $amount);
            Bus::assertDispatchedAfterResponseTimes(ProcessResults::class, $amount);
        }
    }

    public function testWillReturnNotFoundWhenASingleDoesNotExistsInDatabase()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $single = $this->single();

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single(['stake' => $wallet->currency->max_bet])
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertNotFound();
        $response->assertJson([
            'message' => trans('bets.matches.invalid_or_not_found')
        ]);

        $this->assertDatabaseMissing('bets', [
           'user_id' => $user->getKey(),
           'match_id' => $single['match_id']
        ]);
    }

    public function testWillReturnNotFoundWhenAMatchInAMultipleDoesNotExistsInDatabase()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $match = $this->single(['stake' => $wallet->currency->max_bet]);

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'multiples' => [
                $match,
                $this->single(['stake' => $wallet->currency->max_bet])
            ],
            'multipleStake' => $wallet->currency->max_bet,
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertNotFound();
        $response->assertJson([
            'message' => trans('bets.matches.invalid_or_not_found')
        ]);

        $this->assertDatabaseMissing('bets', [
           'user_id' => $user->getKey(),
           'match_id' => $match['match_id']
        ]);
    }

    public function testWillReturnNotFoundWhenASelectionDoesNotExistsInDatabase()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $match = factory(Event::class)->create([
            'time_status' => 0
        ]);

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single([
                    'match_id' => $match->getKey(),
                    'market_id' => 1,
                    'stake' => $wallet->currency->min_bet,
                ])
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertNotFound();
        $response->assertJson([
            'message' => trans('bets.odds.not_found')
        ]);

        $this->assertDatabaseMissing('bets', [
            'user_id' => $user->getKey(),
            'match_id' => $match->getKey()
        ]);
    }

    public function testWillReturnNotFoundWhenAMarketDoesNotExistsInDatabase()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $singleStake = $wallet->currency->max_bet;
        $matches = $this->createMatchesForPlaceBet(1, [
            'time_status' => $live = rand(Event::STATUS_NOT_STARTED, Event::STATUS_IN_PLAY)
        ]);
        $singles = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $singles[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => rand(100, 1000), // Random market id
                'stake' => $singleStake
            ]);
        }

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => $singles,
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertNotFound();
        $response->assertJson([
            'message' => trans('bets.market.not_found')
        ]);
    }

    public function testWillReturnBadRequestIfOddHasChanged()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $match = factory(Event::class)->create([
            'time_status' => Event::STATUS_NOT_STARTED
        ]);
        $stake = $wallet->currency->max_bet;

        $this->createMatchOdds($match, 1);

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single([
                    'match_id' => $match->id,
                    'odd_id' => $match->odds()->first()->id,
                    'odds' => $this->genRandomOdds(),
                    'market_id' => 1,
                    'stake' => $stake
                ])
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'message' => trans('bets.odds.changes')
        ]);
        $this->assertTrue($response->exception instanceof OddsHasChangedException);
    }

    public function testUserCanNotPlaceBetsIfTheyAreNotLoggedIn()
    {
        $response = $this->postJson($this->placeBetRoute());

        $response->assertStatus(401);
    }

    public function testUserCanNotPlaceBetIfTheyAreRestricted()
    {
        $user = factory(User::class)->create([
            'restricted' => true
        ]);

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute());

        $response->assertForbidden();
        $response->assertJson(['message' => trans('user.restricted')]);
    }

    public function testUserCanNotPlaceBetIfTheStakeIsLessThanMinimumLimit()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single([
                    'stake' => $stake = $wallet->currency->min_bet / 2,
                ])
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'singles.0.stake' => trans('bets.stake.min', ['stake' => $wallet->currency->min_bet, 'currency' => $wallet->currency->ticker])
        ]);
    }

    public function testUserCanNotPlaceBetIfTheStakeIsGreaterThanMaxLimit()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 10])->first();

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single([
                    'stake' => $wallet->currency->max_bet * 2
                ])
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'singles.0.stake' => trans('bets.stake.max', ['stake' => $wallet->currency->max_bet, 'currency' => $wallet->currency->ticker])
        ]);
    }

    public function testUserCanNotPlaceBetWhenStakeIsGreaterThanYourBalance()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => $balance = 1])->first();

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'multipleStake' => $balance * 2,
            'multiples' => [
                $this->single(),
                $this->single(),
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'balance' => trans('bets.stake.greater_than_balance')
        ]);
    }

    public function testUserCanNotPlaceBetWhenYourFundsIsTooLow()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 0.000001])->first();

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'multipleStake' => 0.05,
            'multiples' => [$this->single()],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'balance' => trans('user.insufficient_funds')
        ]);
    }

    public function testCanNotPlaceSingleBetsIfASingleStakeIsGreaterThanLimit()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $limit = $wallet->currency->max_bet;

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single(['stake' => $limit * 2])
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertJsonValidationErrors([
            'singles.0.stake' => trans('bets.stake.max', ['stake' => $limit, 'currency' => $wallet->currency->ticker])
        ]);
    }

    public function testCanNotPlaceSingleBetsIfASingleStakeIsLessThanLimit()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $limit = $wallet->currency->min_bet;
        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single(['stake' => $limit / 2])
            ],
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertJsonValidationErrors([
            'singles.0.stake' => trans('bets.stake.min', ['stake' => $limit, 'currency' => $wallet->currency->ticker])
        ]);
    }

    public function testUserCanNotPlaceBetIfTheSumOfAllStakesIsGreaterThanYourBalance()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 1.2])->first();

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => [
                $this->single(['stake' => 0.25]),
                $this->single(['stake' => 0.25]),
                $this->single(['stake' => 0.25]),
            ],
            'multiples' => [
                $this->single(),
                $this->single(),
                $this->single(),
                $this->single(),
            ],
            'multipleStake' => 0.50,
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['balance' => trans('bets.stake.greater_than_balance')]);
    }

    public function testUserCanNotPlaceMultipleBetsIfThereAreMoreThanOneSelectionFromTheSameEvent()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 1])->first();

        Passport::actingAs($user);

        $matchId = rand(1, 100);

        $response = $this->postJson($this->placeBetRoute(), [
            'multiples' => [
                $this->single(['match_id' => $matchId, 'stake' => null]),
                $this->single(['match_id' => $matchId, 'stake' => null]), // should not have two selections from the same event
                $this->single(['stake' => $wallet->currency->min_bet]),
                $this->single(['stake' => $wallet->currency->min_bet]),
            ],
            'multipleStake' => $wallet->currency->max_bet,
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'matches' => trans('bets.multiple.duplicated_event')
        ]);
    }

    public function testUserCanNotPlaceAMultipleIfThereAreLessThan2Matches()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $stake = $wallet->currency->min_bet;
        $amount = 1;
        $matches = $this->createMatchesForPlaceBet($amount, [
            'time_status' => $live = rand(Event::STATUS_NOT_STARTED, Event::STATUS_IN_PLAY)
        ]);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => $stake
            ]);
        }

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'multiples' => $multiples,
            'multipleStake' => $stake,
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'matches' => trans('bets.multiple.invalid_count')
        ]);
    }

    public function testWillReturnBadRequestIfTheProfitIsOverSystemLimit()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 10])->first();
        $stake = $wallet->currency->max_bet;
        $matches = $this->createMatchesForPlaceBet(10, [
            'time_status' => $live = rand(Event::STATUS_NOT_STARTED, Event::STATUS_IN_PLAY)
        ]);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => null
            ]);
        }

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'multiples' => $multiples,
            'multipleStake' => $stake,
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(400);

        $this->assertTrue($response->exception instanceof ProfitOverLimitException);
        $this->assertDatabaseMissing('bets', [
            'user_id' => $user->getKey(),
            'type' => Bet::TYPE_MULTIPLE,
            'stake' => $stake
        ]);
    }

    public function testWillReturnBadRequestIfTheUserPlaceAMultipleWithAsianHandicap()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => $stake = 0.05])->first();
        $marketId = Market::where('key', 'asian_handicap')->first()->id;

        $matches = $this->createMatchesForPlaceBet(5, [
            'time_status' => $live = rand(Event::STATUS_NOT_STARTED, Event::STATUS_IN_PLAY)
        ], ['market_id' => $marketId], $marketId);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => $marketId,
                'stake' => null
            ]);
        }

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'multiples' => $multiples,
            'multipleStake' => $stake,
            'walletId' => $wallet->getKey(),
        ]);

        $response->assertStatus(400);
        $this->assertTrue($response->exception instanceof AsianHandicapOnMultipleException);
        $this->assertEquals(trans('bets.markets.asian_handicap_on_multiple'), $response->exception->getMessage());
    }

    public function testWillReturnBadRequestIfHasOneOrMoreSuspendedSelections()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2)->first();
        $previousUserBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(10, [
            'time_status' => rand(Event::STATUS_NOT_STARTED, Event::STATUS_IN_PLAY)
        ], ['is_suspended' => true]);
        $multiples = [];
        $singles = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $selection = [
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1,
                'stake' => null
            ];

            $multiples[] = $selection;
            $singles[] = array_merge($selection, ['stake' => $wallet->currency->min_bet]);
        }

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => $singles,
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->max_bet,
            'walletId' => $wallet->getKey(),
        ]);

        $user->refresh();
        $wallet->refresh();

        $response->assertStatus(400);
        $this->assertTrue($response->exception instanceof SelectionSuspendedException);
        $this->assertEquals($previousUserBalance, $wallet->balance);
        $this->assertEquals(trans('bets.odds.suspended'), $response->exception->getMessage());
    }

    public function testWillReturnBadRequestIfAEventIsLiveButTheSelectionIsNotLive()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 10])->first();
        $previousUserBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(10, [
            'time_status' => Event::STATUS_IN_PLAY
        ], ['is_suspended' => false, 'is_live' => false]);
        $multiples = [];
        $singles = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $selection = [
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1,
                'stake' => null
            ];

            $multiples[] = $selection;
            $singles[] = array_merge($selection, ['stake' => $wallet->currency->min_bet]);
        }

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => $singles,
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->max_bet,
            'walletId' => $wallet->getKey(),
        ]);

        $user->refresh();
        $wallet->refresh();

        $response->assertStatus(400);
        $this->assertTrue($response->exception instanceof UnavailableSelectionException);
        $this->assertEquals($previousUserBalance, $wallet->balance);
        $this->assertEquals(trans('bets.odds.unavailable'), $response->exception->getMessage());
    }

    public function testWillReturnBadRequestIfAEventIsNotStartedButTheSelectionIsLive()
    {
        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 10])->first();
        $previousUserBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(10, [
            'time_status' => Event::STATUS_NOT_STARTED
        ], ['is_suspended' => false, 'is_live' => true]);
        $multiples = [];
        $singles = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $selection = [
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1,
                'stake' => null
            ];

            $multiples[] = $selection;
            $singles[] = array_merge($selection, ['stake' => $wallet->currency->max_bet]);
        }

        Passport::actingAs($user);

        $response = $this->postJson($this->placeBetRoute(), [
            'singles' => $singles,
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->min_bet,
            'walletId' => $wallet->getKey(),
        ]);

        $user->refresh();
        $wallet->refresh();

        $response->assertStatus(400);
        $this->assertTrue($response->exception instanceof UnavailableSelectionException);
        $this->assertEquals($previousUserBalance, $wallet->balance);
        $this->assertEquals(trans('bets.odds.unavailable'), $response->exception->getMessage());
    }

    public function testUserCanNotPlaceALiveBetIfTheDelayedBetGetExpired()
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 0.05])->first();
        $previouslyBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(2, [
            'time_status' => Event::STATUS_IN_PLAY
        ]);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => null
            ]);
        }

        Passport::actingAs($user);

        $payload = [
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->min_bet,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);
        $response->assertSuccessful();

        $bettingService = new BettingService();

        $bettingService->forgetBetDelay($user, $response->json('data.betUniqueId'));

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload);
        $delayedBetResponse->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user->fresh();
        $wallet->refresh();

        $this->assertEquals(trans('bets.expired'), $delayedBetResponse->exception->getMessage());
        $this->assertInstanceOf(BetExpiredException::class, $delayedBetResponse->exception);
        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals($previouslyBalance, $wallet->balance);
    }

    public function testUserCanNotSendAWrongHashForDelayedBets()
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 0.05])->first();
        $previouslyBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(2, [
            'time_status' => Event::STATUS_IN_PLAY
        ]);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => null
            ]);
        }

        Passport::actingAs($user);

        $payload = [
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->min_bet,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);

        $response->assertSuccessful();

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload, null, 'WrongHash');
        $delayedBetResponse->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user->fresh();
        $wallet->fresh();

        $this->assertInstanceOf(WrongBetHashException::class, $delayedBetResponse->exception);
        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals($previouslyBalance, $wallet->balance);
    }

    public function testUserCanNotModifyTheSelectionsAndReuseAPreviousHash()
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 0.05])->first();
        $previouslyBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(2, [
            'time_status' => Event::STATUS_IN_PLAY
        ]);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => null
            ]);
        }

        Passport::actingAs($user);

        $payload = [
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->min_bet,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);
        $response->assertSuccessful();

        $newMatches = $this->createMatchesForPlaceBet(2, [
            'time_status' => Event::STATUS_IN_PLAY
        ]);

        foreach ($newMatches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => null
            ]);
        }

        $payload = [
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->max_bet,
            'walletId' => $wallet->getKey(),
        ];

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload);
        $delayedBetResponse->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user->fresh();
        $wallet->fresh();

        $this->assertEquals(trans('bets.wrong_hash'), $delayedBetResponse->exception->getMessage());
        $this->assertInstanceOf(WrongBetHashException::class, $delayedBetResponse->exception);
        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals($previouslyBalance, $wallet->balance);
    }

    public function testUserCanNotModifyTheSelectedWalletAndReuseAPreviousHash()
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallets = $this->createUserWallets($user, 2, ['balance' => 10]);
        $wallet = $wallets->first();

        $previouslyBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(3, [
            'time_status' => Event::STATUS_IN_PLAY
        ]);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => null
            ]);
        }

        Passport::actingAs($user);

        $payload = [
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->max_bet,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);
        $response->assertSuccessful();

        $payload = [
            'multiples' => $multiples,
            'multipleStake' => 1,
            'walletId' => $wallets->last()->getKey(), // Simulate user changing active wallet during bet delay
        ];

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload);
        $delayedBetResponse->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $user->fresh();
        $wallet->fresh();

        $this->assertEquals(trans('bets.wrong_hash'), $delayedBetResponse->exception->getMessage());
        $this->assertInstanceOf(WrongBetHashException::class, $delayedBetResponse->exception);
        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals($previouslyBalance, $wallet->balance);
        // Check if the other wallet balance was not affected
        $this->assertEquals($previouslyBalance, $wallets->last()->balance);
    }

    public function testUserShouldWaitBeforePlaceADelayedBet()
    {
        Bus::fake();

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, 2, ['balance' => 10])->first();
        $previouslyBalance = $wallet->balance;
        $matches = $this->createMatchesForPlaceBet(3, [
            'time_status' => Event::STATUS_IN_PLAY
        ]);
        $multiples = [];

        foreach ($matches as $match) {
            $matchOdds = $match->odds()->first();
            $multiples[] = $this->single([
                'match_id' => $match->id,
                'odd_id' => $matchOdds->id,
                'odds' => $matchOdds->odds,
                'market_id' => 1, // Full Time result
                'stake' => null
            ]);
        }

        Passport::actingAs($user);

        $payload = [
            'multiples' => $multiples,
            'multipleStake' => $wallet->currency->max_bet,
            'walletId' => $wallet->getKey(),
        ];

        $response = $this->postJson($this->placeBetRoute(), $payload);
        $response->assertSuccessful();

        $delayedBetResponse = $this->handleLiveBettingDelay($response, $payload, 4000);
        $delayedBetResponse->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);

        $user->fresh();
        $wallet->fresh();

        $this->assertEquals(trans('bets.delayed_bet_sent_too_early'), $delayedBetResponse->exception->getMessage());
        $this->assertInstanceOf(DelayedBetSentTooEarlyException::class, $delayedBetResponse->exception);
        $this->assertDatabaseCount('bets', 0);
        $this->assertEquals($previouslyBalance, $wallet->balance);
    }
}
