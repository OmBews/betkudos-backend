<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;

/**
 * Class TableTennisMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR TENNIS: 1901-2000
 *
 * @see SoccerMarketsSeeder
 */
class TableTennisMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 1901,
                'sport_id' => Sport::TABLE_TENNIS_SPORT_ID,
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
            ]
        ];
    }
}
