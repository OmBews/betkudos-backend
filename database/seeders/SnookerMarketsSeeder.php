<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class SnookerMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR RUGBY LEAGUE: 1501-1600
 *
 * @see SoccerMarketsSeeder
 */
class SnookerMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 1501,
                'sport_id' => Sport::SNOOKER_SPORT_ID,
                'name' => 'Winner',
                'key' => 'to_win_match',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'headers' => ['1', '2'],
                'on_live_betting' => false,
                'layout' => Market::DEFAULT_LAYOUT
            ]
        ];
    }
}
