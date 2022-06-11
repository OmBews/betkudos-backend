<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class IceHockeyMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR ICE HOCKEY: 601-700
 *
 * @see SoccerMarketsSeeder
 */
class IceHockeyMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 601,
                'sport_id' => Sport::ICE_HOCKEY_SPORT_ID,
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
