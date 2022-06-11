<?php

namespace Database\Seeders;

use App\Models\Casino\Games\GameCategory;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            CryptoCurrenciesTableSeeder::class,
            RolesAndPermissionsSeeder::class,
            SportsTableSeeder::class,
            SportCategoriesTableSeeder::class,
            CountryTableSeeder::class,
            MarketGroupsTableSeeder::class,
            SoccerMarketsSeeder::class,
            FutsalMarketsSeeder::class,
            BoxingMmaMarketsSeeder::class,
            AmericanFootballMarketsSeeder::class,
            IceHockeyMarketsSeeder::class,
            CricketMarketsSeeder::class,
            DartsMarketsSeeder::class,
            BasketballMarketsSeeder::class,
            VolleyballMarketsSeeder::class,
            HandballMarketsSeeder::class,
            RugbyUnionMarketsSeeder::class,
            RugbyLeagueMarketsSeeder::class,
            SnookerMarketsSeeder::class,
            TennisMarketsSeeder::class,
            ESportsMarketsSeeder::class,
            TableTennisMarketsSeeder::class,
            GameCategorySeeder::class,
        ]);
    }
}
