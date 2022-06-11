<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;

/**
 * Class TennisMarketsSeeder
 *
 * PREDEFINED IDS RANGE FOR TENNIS: 1601-1800
 *
 * @see SoccerMarketsSeeder
 */
class TennisMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return [
            [
                'id' => 1601,
                'sport_id' => Sport::TENNIS_SPORT_ID,
                'name' => 'To Win Match',
                'key' => 'to_win_match',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'headers' => ['1', '2'],
                'priority' => 0,
                'on_live_betting' => false,
                'layout' => Market::DEFAULT_LAYOUT
            ],
            [
                'id' => 1602,
                'sport_id' => Sport::TENNIS_SPORT_ID,
                'name' => 'Games Handicap',
                'key' => 'match_handicap_(games)',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'headers' => ['1', '2'],
                'priority' => 3,
                'on_live_betting' => true,
                'layout' => Market::DEFAULT_LAYOUT
            ],
            [
                'id' => 1603,
                'sport_id' => Sport::TENNIS_SPORT_ID,
                'name' => 'Total Games',
                'key' => 'total_games_2_way',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'headers' => ['Over', 'Under'],
                'priority' => 1,
                'on_live_betting' => false,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
            [
                'id' => 1604,
                'sport_id' => Sport::TENNIS_SPORT_ID,
                'name' => 'Set Betting',
                'key' => 'set_betting',
                'featured_header' => '2-0',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => false,
                'headers' => ['1', '2'],
                'priority' => 2,
                'on_live_betting' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
            [
                'id' => 1605,
                'sport_id' => Sport::TENNIS_SPORT_ID,
                'name' => 'To Win',
                'key' => 'to_win',
                'active' => true,
                'featured_header' => 'Match',
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'headers' => ['1', '2'],
                'priority' => 4,
                'on_live_betting' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
            [
                'id' => 1606,
                'sport_id' => Sport::TENNIS_SPORT_ID,
                'name' => 'Total Games',
                'key' => 'total_games_in_match',
                'active' => true,
                'market_groups' => 'main',
                'popular' => true,
                'featured' => true,
                'headers' => ['Over', 'Under'],
                'priority' => 2,
                'on_live_betting' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
        ];
    }
}
