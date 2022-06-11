<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;
/**
 * Class RugbyLeagueMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR RUGBY LEAGUE: 1401-1500
 *
 * @see SoccerMarketsSeeder
 */
class RugbyLeagueMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 1401,
                'sport_id' => Sport::RUGBY_LEAGUE_SPORT_ID,
                'name' => 'Game Betting 2-way',
                'key' => 'game_betting_2_way',
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
