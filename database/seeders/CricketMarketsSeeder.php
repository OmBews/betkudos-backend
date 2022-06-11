<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class CricketMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR CRICKET: 701-800
 *
 * @see SoccerMarketsSeeder
 */
class CricketMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 701,
                'sport_id' => Sport::CRICKET_SPORT_ID,
                'name' => 'Match Winner',
                'key' => 'to_win_the_match',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'headers' => ['1', '2'],
                'on_live_betting' => true,
                'layout' => Market::DEFAULT_LAYOUT
            ]
        ];
    }
}
