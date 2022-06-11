<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sports\Sport;

class SportsTableSeeder extends Seeder
{
    private const SPORTS = [
        [
            'id' => Sport::AMERICAN_FOOTBALL_SPORT_ID,
            'bet365_id' => Sport::AMERICAN_FOOTBALL_SPORT_ID,
            'name' => 'american football',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 60, // days
        ],
//        [
//            'id' => 16,
//            'name' => 'baseball',
//            'active' => true,
//        ],
        [
            'id' => Sport::BASKETBALL_SPORT_ID,
            'bet365_id' => Sport::BASKETBALL_SPORT_ID,
            'name' => 'basketball',
            'active' => true,
            'on_live_betting' => true,
            'priority' => 97,
        ],
        [
            'id' => Sport::BOXING_MMA_SPORT_ID,
            'bet365_id' => Sport::BOXING_MMA_SPORT_ID,
            'name' => 'boxing',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 90, // days
        ],
        [
            'id' => Sport::CRICKET_SPORT_ID,
            'bet365_id' => Sport::CRICKET_SPORT_ID,
            'name' => 'cricket',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 15, // days
        ],
        [
            'id' => Sport::DARTS_SPORT_ID,
            'bet365_id' => Sport::DARTS_SPORT_ID,
            'name' => 'darts',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 30, // days
        ],
        [
            'id' => Sport::E_SPORTS_SPORT_ID,
            'bet365_id' => Sport::E_SPORTS_SPORT_ID,
            'name' => 'e-sports',
            'active' => true,
        ],
        [
            'id' => Sport::FUTSAL_SPORT_ID,
            'bet365_id' => Sport::FUTSAL_SPORT_ID,
            'name' => 'futsal',
            'active' => true,
        ],
        [
            'id' => Sport::HANDBALL_SPORT_ID,
            'bet365_id' => Sport::HANDBALL_SPORT_ID,
            'name' => 'handball',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 15, // days
        ],
        [
            'id' => Sport::ICE_HOCKEY_SPORT_ID,
            'bet365_id' => Sport::ICE_HOCKEY_SPORT_ID,
            'name' => 'ice hockey',
            'on_live_betting' => false,
            'active' => true,
        ],
        [
            'id' => Sport::MMA_SPORT_ID,
            'bet365_id' => Sport::BOXING_MMA_SPORT_ID,
            'name' => 'MMA',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 90
        ],
        [
            'id' => Sport::RUGBY_LEAGUE_SPORT_ID,
            'bet365_id' => Sport::RUGBY_LEAGUE_SPORT_ID,
            'name' => 'rugby league',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 30 // days
        ],
        [
            'id' => Sport::RUGBY_UNION_SPORT_ID,
            'bet365_id' => Sport::RUGBY_UNION_SPORT_ID,
            'name' => 'rugby union',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 30 // days
        ],
        [
            'id' => Sport::SOCCER_SPORT_ID,
            'bet365_id' => Sport::SOCCER_SPORT_ID,
            'name' => 'soccer',
            'active' => true,
            'on_live_betting' => true,
            'upcoming_preview_limit' => 8,
            'priority' => 99,
        ],
        [
            'id' => Sport::SNOOKER_SPORT_ID,
            'bet365_id' => Sport::SNOOKER_SPORT_ID,
            'name' => 'snooker',
            'active' => true,
            'on_live_betting' => false,
            'time_frame' => 15 // days
        ],
        [
            'id' => Sport::TABLE_TENNIS_SPORT_ID,
            'bet365_id' => Sport::TABLE_TENNIS_SPORT_ID,
            'name' => 'table tennis',
            'on_live_betting' => false,
            'active' => true,
        ],
        [
            'id' => Sport::TENNIS_SPORT_ID,
            'bet365_id' => Sport::TENNIS_SPORT_ID,
            'name' => 'tennis',
            'active' => true,
            'on_live_betting' => true,
            'time_frame' => 15,
            'priority' => 98,
        ],
        [
            'id' => Sport::VOLLEYBALL_SPORT_ID,
            'bet365_id' => Sport::VOLLEYBALL_SPORT_ID,
            'name' => 'volleyball',
            'on_live_betting' => false,
            'active' => true,
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach (self::SPORTS as $sport) {
            Sport::query()->updateOrCreate(['id' => $sport['id']], $sport);
        }
    }
}
