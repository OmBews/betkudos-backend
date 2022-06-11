<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;

/**
 * Class BoxingMmaMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR BOX/MMA: 301-400
 *
 * @see SoccerMarketsSeeder
 */
class BoxingMmaMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 301,
                'sport_id' => Sport::BOXING_MMA_SPORT_ID,
                'name' => 'To Win Fight',
                'key' => 'to_win_fight',
                'market_groups' => 'main',
                'active' => true,
                'popular' => true,
                'on_live_betting' => true,
                'featured' => true,
                'headers' => ['1', '2'],
                'layout' => Market::DEFAULT_LAYOUT
            ]
        ];
    }
}
