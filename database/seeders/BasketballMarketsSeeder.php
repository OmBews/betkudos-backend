<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;

/**
 * Class BasketballMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR BASKETBALL: 901-1100
 *
 * @see SoccerMarketsSeeder
 */
class BasketballMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 901,
                'sport_id' => Sport::BASKETBALL_SPORT_ID,
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
