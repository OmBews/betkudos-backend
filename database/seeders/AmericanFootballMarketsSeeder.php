<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;

/**
 * Class AmericanFootballMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR AMERICAN FOOTBALL: 401-600
 *
 * @see SoccerMarketsSeeder
 */
class AmericanFootballMarketsSeeder extends MarketsTableSeeder
{
   protected function markets(): array
   {
       return [
         [
             'id' => 401,
             'sport_id' => Sport::AMERICAN_FOOTBALL_SPORT_ID,
             'name' => 'Game Lines',
             'key' => 'game_lines',
             'active' => true,
             'market_groups' => 'main',
             'popular' => true,
             'featured' => true,
             'featured_header' => 'Money Line',
             'headers' => ['1', '2'],
             'on_live_betting' => true,
             'layout' => Market::OVER_UNDER_LAYOUT
         ]
       ];
   }
}
