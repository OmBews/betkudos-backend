<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class DartsMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR CRICKET: 801-900
 *
 * @see SoccerMarketsSeeder
 */
class DartsMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 801,
                'sport_id' => Sport::DARTS_SPORT_ID,
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
