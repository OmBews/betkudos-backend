<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class HandballMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR HANDBALL: 1201-1300
 *
 * @see SoccerMarketsSeeder
 */
class HandballMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 1201,
                'sport_id' => Sport::HANDBALL_SPORT_ID,
                'name' => 'Game Lines',
                'key' => 'game_lines',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'featured_header' => 'To Win',
                'headers' => ['1', '2'],
                'on_live_betting' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ]
        ];
    }
}
