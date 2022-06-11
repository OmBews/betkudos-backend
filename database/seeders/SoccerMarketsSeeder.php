<?php

namespace Database\Seeders;

use App\Models\Markets\Market;
use App\Models\Sports\Sport;
use Illuminate\Database\Seeder;

/**
 * Class SoccerMarketsSeeder
 *
 * Each market must have a fixed id following the predefined range.
 * For example for Soccer we have 1-200. For Futsal we have 201-300
 * and so on. Some sports may have up to 200 ids reserved that depends
 * on how popular it is.
 *
 * PREDEFINED IDS RANGE FOR SOCCER: 1-200
 */
class SoccerMarketsSeeder extends MarketsTableSeeder
{
    protected function markets(): array
    {
        return array_merge(
            $this->main(),
            $this->goals(),
            $this->player(),
            $this->half(),
            $this->specials()
        );
    }

    private function main(): array
    {
        return [
            [
                'id' => 1,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Fulltime Result',
                'key' => 'full_time_result',
                'market_groups' => 'main',
                'active' => true,
                'popular' => true,
                'on_live_betting' => true,
                'featured' => true,
                'priority' => 1,
                'headers' => ['1', 'X', '2'],
            ],
            [
                'id' => 2,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Double Chance',
                'key' => 'double_chance',
                'market_groups' => 'main',
                'active' => true,
                'popular' => true,
                'on_live_betting' => true,
            ],
            [
                'id' => 3,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Correct Score',
                'key' => 'correct_score',
                'market_groups' => 'main',
                'active' => true,
                'popular' => true,
                'on_live_betting' => false,
                'layout' => Market::SCORE_LAYOUT
            ],
            [
                'id' => 4,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Both Teams to Score',
                'key' => 'both_teams_to_score',
                'market_groups' => 'main,goals',
                'active' => true,
                'popular' => true,
                'on_live_betting' => true,
                'priority' => 2,
                'headers' => ['YES', 'NO'],
                'featured' => false
            ],
            [
                'id' => 5,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Half Time/Full Time',
                'key' => 'half_time_full_time',
                'market_groups' => 'main',
                'active' => true,
                'popular' => true,
                'on_live_betting' => false,
                'layout' => Market::INLINE_LAYOUT
            ],
            [
                'id' => 6,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Goals Over/Under',
                'key' => 'goals_over_under',
                'live_key' => 'match_goals',
                'market_groups' => 'main,goals',
                'headers' => ['Over', 'Under'],
                'active' => true,
                'featured' => true,
                'priority' => 3,
                'popular' => true,
                'on_live_betting' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
            [
                'id' => 7,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Asian Handicap',
                'key' => 'asian_handicap',
                'market_groups' => 'asian_lines',
                'active' => true,
                'popular' => true,
                'on_live_betting' => false
            ],
            [
                'id' => 8,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Draw No Bet',
                'key' => 'draw_no_bet',
                'market_groups' => 'main',
                'active' => true,
                'popular' => true,
                'on_live_betting' => true
            ],
            [
                'id' => 9,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => 'Winning Margin',
                'key' => 'winning_margin',
                'market_groups' => 'main',
                'active' => true,
                'popular' => true,
                'on_live_betting' => false
            ],
            [
                'id' => 10,
                'sport_id' => Sport::SOCCER_SPORT_ID,
                'name' => '10 Minute Result',
                'key' => '10_minute_result',
                'market_groups' => 'minutes',
                'active' => true,
                'popular' => true,
                'on_live_betting' => false
            ],
        ];
    }

    private function player()
    {
        return [
            [
                'id' => 11,
                'sport_id' => 1,
                'name' => 'Goalscorers',
                'key' => 'goalscorers',
                'market_groups' => 'player,main',
                'active' => true,
                'layout' => Market::GOALSCORERS_LAYOUT
            ],
        ];
    }

    private function goals()
    {
        return [
            [
                'id' => 12,
                'sport_id' => 1,
                'name' => 'Alternative Total Goals',
                'key' => 'alternative_total_goals',
                'live_key' => 'alternative_match_goals',
                'market_groups' => 'goals',
                'headers' => ['Over', 'Under'],
                'active' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
            [
                'id' => 13,
                'sport_id' => 1,
                'name' => 'Result / Total Goals',
                'key' => 'result_total_goals',
                'market_groups' => 'goals',
                'active' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
            [
                'id' => 14,
                'sport_id' => 1,
                'name' => 'First Half Goals',
                'key' => 'first_half_goals',
                'market_groups' => 'goals,half',
                'active' => true,
                'on_live_betting' => true,
                'layout' => Market::OVER_UNDER_LAYOUT
            ],
            [
                'id' => 15,
                'sport_id' => 1,
                'name' => 'Goals Odd/Even',
                'key' => 'goals_odd_even',
                'market_groups' => 'goals',
                'active' => true,
                'on_live_betting' => true,
            ],
            [
                'id' => 16,
                'sport_id' => 1,
                'name' => 'Home Team Exact Goals',
                'key' => 'home_team_exact_goals',
                'market_groups' => 'goals',
                'active' => true,
                'layout' => Market::INLINE_LAYOUT
            ],
            [
                'id' => 17,
                'sport_id' => 1,
                'name' => 'Away Team Exact Goals',
                'key' => 'away_team_exact_goals',
                'market_groups' => 'goals',
                'active' => true,
                'layout' => Market::INLINE_LAYOUT
            ],
            [
                'id' => 18,
                'sport_id' => 1,
                'name' => 'Last Team to Score',
                'key' => 'last_team_to_score',
                'market_groups' => 'goals',
                'active' => true,
                'on_live_betting' => true,
            ],
        ];
    }

    private function half()
    {
        return [
            [
                'id' => 19,
                'sport_id' => 1,
                'name' => 'Half Time Result',
                'key' => 'half_time_result',
                'market_groups' => 'half',
                'active' => true,
            ],
            [
                'id' => 20,
                'sport_id' => 1,
                'name' => 'Half Time Double Chance',
                'key' => 'half_time_double_chance',
                'market_groups' => 'half',
                'active' => true,
            ],
            [
                'id' => 21,
                'sport_id' => 1,
                'name' => 'Half Time Correct Score',
                'key' => 'half_time_correct_score',
                'market_groups' => 'half',
                'active' => true,
                'layout' => Market::SCORE_LAYOUT
            ],
            [
                'id' => 22,
                'sport_id' => 1,
                'name' => 'Both Teams to Score in 1st Half',
                'key' => 'both_teams_to_score_in_1st_half',
                'market_groups' => 'half',
                'active' => true,
            ],
            [
                'id' => 23,
                'sport_id' => 1,
                'name' => 'Both Teams to Score in 2nd Half',
                'key' => 'both_teams_to_score_in_2nd_half',
                'market_groups' => 'half',
                'active' => true,
            ],
            [
                'id' => 24,
                'sport_id' => 1,
                'name' => '1st Half Goals Odd/Even',
                'key' => '1st_half_goals_odd_even',
                'market_groups' => 'half,goals',
                'active' => true,
            ],
            [
                'id' => 25,
                'sport_id' => 1,
                'name' => 'Half with Most Goals',
                'key' => 'half_with_most_goals',
                'market_groups' => 'half,goals',
                'active' => true,
            ],
        ];
    }

    private function specials(): array
    {
        return [
            [
                'id' => 26,
                'sport_id' => 1,
                'name' => 'To Win From Behind',
                'key' => 'to_win_from_behind',
                'market_groups' => 'specials',
                'active' => true,
            ],
            [
                'id' => 27,
                'sport_id' => 1,
                'name' => 'To Win to Nil',
                'key' => 'to_win_to_nil',
                'market_groups' => 'specials',
                'active' => true,
            ],
            [
                'id' => 28,
                'sport_id' => 1,
                'name' => 'To Win Either Half',
                'key' => 'to_win_either_half',
                'market_groups' => 'specials',
                'active' => true,
            ],
            [
                'id' => 29,
                'sport_id' => 1,
                'name' => 'To Win Both Halves',
                'key' => 'to_win_both_halves',
                'market_groups' => 'specials',
                'active' => true,
            ],
            [
                'id' => 30,
                'sport_id' => 1,
                'name' => 'To Score in Both Halves',
                'key' => 'to_score_in_both_halves',
                'market_groups' => 'specials',
                'active' => true,
            ],
        ];
    }
}
