<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class ESportsMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR TENNIS: 1801-1900
 *
 * @see SoccerMarketsSeeder
 */
class ESportsMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 1801,
                'sport_id' => Sport::E_SPORTS_SPORT_ID,
                'name' => 'Match Lines',
                'key' => 'match_lines',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'featured_header' => 'To Win',
                'headers' => ['1', '2'],
                'on_live_betting' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
        ];
    }
}
