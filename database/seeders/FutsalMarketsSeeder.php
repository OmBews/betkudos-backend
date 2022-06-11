<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class FutsalMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR FUTSAL: 201-300
 *
 * @see SoccerMarketsSeeder
 */
class FutsalMarketsSeeder extends MarketsTableSeeder
{
  protected function markets(): array
  {
      return [
          [
              'id' => 201,
              'sport_id' => Sport::FUTSAL_SPORT_ID,
              'name' => '3 Way',
              'key' => '3_way',
              'market_groups' => 'main',
              'active' => true,
              'popular' => true,
              'on_live_betting' => true,
              'featured' => true,
              'featured_header' => 'Result',
              'headers' => ['1', 'X', '2'],
              'layout' => Market::OVER_UNDER_LAYOUT,
          ],
          [
              'id' => 202,
              'sport_id' => Sport::FUTSAL_SPORT_ID,
              'name' => 'Game Lines',
              'key' => 'game_lines',
              'market_groups' => 'main',
              'active' => true,
              'popular' => true,
              'on_live_betting' => true,
              'layout' => Market::OVER_UNDER_LAYOUT,
          ],
      ];
  }
}
