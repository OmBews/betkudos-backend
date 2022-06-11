<?php

namespace Tests\Feature\Console\Commands\SB;

use App\Contracts\Services\BetProcessorService;
use App\Models\Bets\Bet;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Currencies\CryptoCurrency;
use App\Models\Markets\Market;
use App\Models\Markets\MarketOdd;
use App\Models\Events\Event;
use App\Models\Events\Results\Result;
use App\Models\Events\Stats\Stats;
use App\Models\Teams\Team;
use App\Models\Users\User;
use App\Models\Wallets\Wallet;
use Database\Seeders\CryptoCurrenciesTableSeeder;
use Database\Seeders\MarketGroupsTableSeeder;
use Database\Seeders\SoccerMarketsSeeder;
use Database\Seeders\SportsTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Tests\TestCase;

class OpenBetsTest extends TestCase
{
    use RefreshDatabase;

    private $signature = 'sb:open-bets';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SportsTableSeeder::class);
        $this->seed(MarketGroupsTableSeeder::class);
        $this->seed(SoccerMarketsSeeder::class);
        $this->seed(CryptoCurrenciesTableSeeder::class);
    }

    protected function createUserWallets(User $user, $amount = 1, array $data = []): \Illuminate\Support\Collection
    {
        $wallets = [];

        foreach (CryptoCurrency::all()->take($amount) as $currency) {
            $wallets[] = Wallet::factory()->create(array_merge(['user_id' => $user->getKey(), 'crypto_currency_id' => $currency->getKey()], $data));
        }

        return collect($wallets);
    }

    protected function makeUserBet(Bet $bet, array $matchAttrs, Market $market, array $odds = [])
    {
        $home = factory(Team::class)->create();
        $away = factory(Team::class)->create();
        $match = factory(Event::class)->create(
            array_merge(['home_team_id' => $home->bet365_id, 'away_team_id' => $away->bet365_id], $matchAttrs)
        );
        $odds = $match->odds()->save(
            factory(MarketOdd::class)->make(array_merge([
                'market_id' => $market->getKey()
            ], $odds))
        );
        $match->stats()->save((new Stats())->fill(['stats' => [], 'events' => json_encode([])]));
        $selection = $bet->selections()->save(
            factory(BetSelection::class)->make([
                'bet_id' => $bet->getKey(),
                'market_id' => $market->getKey(),
                'match_id' => $match->getKey(),
                'status' => BetSelection::STATUS_OPEN,
                'odd_id' => $odds->getKey(),
                'header' => $odds->header,
                'name' => $odds->name,
                'handicap' => $odds->handicap,
            ])
        );

        return [$bet, $selection, $odds, $match];
    }

    public function testWillVoidABetWhenAMatchStatusIsVoidable()
    {
        $command = $this->artisan($this->signature);

        $user = factory(User::class)->create(['balance' => 100]);
        $wallet = $this->createUserWallets($user, data: ['balance' => 100])->first();
        $bet = $user->bets()->save(
            factory(Bet::class)->make(['wallet_id' => $wallet->getKey(), 'status' => Bet::STATUS_OPEN, 'user_id' => $user->getKey()])
        );
        $matchAttrs = ['time_status' => Arr::random(BetProcessorService::VOIDABLE_STATUSES)];
        $market = (new Market)->fullTimeResult();
        [$bet, $selection , $odds, $match] = $this->makeUserBet(
            $bet, $matchAttrs, $market
        );
        $match->result()->save(
            Result::query()->make(['single_score' => '0-0', 'scores' => '{}', 'bet365_match_id' => Str::random()])
        );
        $expectedUserBalance = $wallet->balance + $bet->stake;

        $command->assertExitCode(0);
        $command->execute();

        $bet->refresh();
        $wallet->refresh();
        $selection->refresh();

        $this->assertEquals(Bet::STATUS_VOID, $bet->status);
        $this->assertEquals(BetSelection::STATUS_VOID, $selection->status);
        $this->assertEquals($expectedUserBalance, $wallet->balance);
    }

    public function testWillRecalculateTheProfitWhenASelectionInAMultipleIsVoid()
    {
        $command = $this->artisan($this->signature);

        $user = factory(User::class)->create(['balance' => 100]);
        $wallet = $this->createUserWallets($user, data: ['balance' => 100])->first();
        $bet = $user->bets()->save(
            factory(Bet::class)->make([
                'wallet_id' => $wallet->getKey(),
                'type' => 'multiple',
                'status' => Bet::STATUS_OPEN,
                'user_id' => $user->getKey()
            ])
        );
        $matchAttrs = ['time_status' => Event::STATUS_ENDED];
        $market = Market::where('key', 'draw_no_bet')->first();
        $matches = [];
        $selections = [];
        collect([1, 2, 3])->each(function () use ($market, $matchAttrs, $bet, &$matches, &$selections) {
            [, $selection , , $match] = $this->makeUserBet(
                $bet, $matchAttrs, $market, ['name' => '1']
            );
            $matches[] = $match;
            $selections[] = $selection;
        });
        $voidSelection = Arr::first($selections);
        $match = Arr::first($matches);
        $match->result()->save(
            Result::query()->make(['single_score' => '0-0', 'scores' => '{}', 'bet365_match_id' => Str::random()])
        );

        foreach (Arr::except($matches, 0) as $m) {
            $m->result()->save(
                Result::query()->make(['single_score' => '1-0', 'scores' => '{}', 'bet365_match_id' => Str::random()])
            );
        }

        $command->assertExitCode(0);
        $command->execute();

        $oldBalance = $wallet->balance;
        $oldProfit = $bet->profit;

        $bet->refresh();
        $voidSelection->refresh();
        $wallet->refresh();

        $this->assertEquals(BetSelection::STATUS_VOID, $voidSelection->status);
        $this->assertEquals(Bet::STATUS_WON, $bet->status);
        $this->assertNotEquals($oldBalance, $wallet->balance);
        $this->assertNotEquals($oldProfit, $bet->profit);
    }

    public function testWillHalfWonABetWhenTheBetHasADrawInAAsianHandicapForTheUnderdog()
    {
        $command = $this->artisan($this->signature);

        $user = factory(User::class)->create();
        $wallet = $this->createUserWallets($user, data: ['balance' => 100])->first();
        $bet = $user->bets()->save(
            factory(Bet::class)->make([
                'type' => 'single',
                'wallet_id' => $wallet->getKey(),
                'status' => Bet::STATUS_OPEN,
                'user_id' => $user->getKey()
            ])
        );
        $matchAttrs = ['time_status' => Event::STATUS_ENDED];
        $market = Market::where('key', 'asian_handicap')->first();
        [, $selection , , $match] = $this->makeUserBet(
            $bet, $matchAttrs, $market, ['name' => '+0.25', 'header' => 'Home']
        );
        $match->result()->save(
            Result::query()->make(['single_score' => '0-0', 'scores' => '{}', 'bet365_match_id' => Str::random()])
        );

        $command->assertExitCode(0);
        $command->execute();

        $oldBalance = $wallet->balance;
        $oldProfit = $bet->profit;

        $bet->refresh();
        $selection->refresh();
        $wallet->refresh();

        $this->assertEquals(Bet::STATUS_HALF_WON, $bet->status);
        $this->assertEquals(BetSelection::STATUS_HALF_WON, $selection->status);
        $this->assertNotEquals($oldProfit, $bet->profit);
        $this->assertEquals($oldBalance + $bet->profit, $wallet->balance);
    }

    public function testWillHalfLossABetWhenTheBetHasADrawInAAsianHandicapForThePreferredTeam()
    {
        $command = $this->artisan($this->signature);

        $user = factory(User::class)->create(['balance' => 100]);
        $wallet = $this->createUserWallets($user, data: ['balance' => 100])->first();
        $bet = $user->bets()->save(
            factory(Bet::class)->make([
                'type' => 'single',
                'wallet_id' => $wallet->getKey(),
                'status' => Bet::STATUS_OPEN,
                'user_id' => $user->getKey()
            ])
        );
        $matchAttrs = ['time_status' => Event::STATUS_ENDED];
        $market = Market::where('key', 'asian_handicap')->first();
        [, $selection , , $match] = $this->makeUserBet(
            $bet, $matchAttrs, $market, ['name' => '-0.25', 'header' => 'Home']
        );
        $match->result()->save(
            Result::query()->make(['single_score' => '0-0', 'scores' => '{}', 'bet365_match_id' => Str::random()])
        );

        $command->assertExitCode(0);
        $command->execute();

        $oldBalance = $wallet->balance;
        $oldStake = $bet->stake;

        $bet->refresh();
        $selection->refresh();
        $wallet->refresh();

        $this->assertEquals(Bet::STATUS_HALF_LOST, $bet->status);
        $this->assertEquals(BetSelection::STATUS_HALF_LOST, $selection->status);
        $this->assertEquals($oldStake / 2, $bet->stake);
        $this->assertEquals($oldBalance + ($oldStake / 2), $wallet->balance);
    }
}
