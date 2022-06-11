<?php

use App\Processors\AsianLines\AsianHandicapProcessor;
use App\Processors\AsianLines\MatchGamesHandicapProcessor;
use App\Processors\DoubleChanceProcessor;
use App\Processors\DrawNoBetProcessor;
use App\Processors\GameBetting2Way\GameBetting2WayProcessor;
use App\Processors\GameLines\GameLinesProcessor;
use App\Processors\Games\TotalGamesInMatchProcessor;
use App\Processors\Goals\FirstHalfGoalsOddEvenProcessor;
use App\Processors\Goals\FirstHalfGoalsOverUnderProcessor;
use App\Processors\Goals\GoalsOddEvenProcessor;
use App\Processors\Goals\GoalsOverUnderProcessor;
use App\Processors\Goals\ResultTotalGoalsProcessor;
use App\Processors\GoalscorersProcessor;
use App\Processors\HalfTime\HalfTimeCorrectScoreProcessor;
use App\Processors\HalfTime\HalfTimeDoubleChanceProcessor;
use App\Processors\HalfTime\HalfTimeResultProcessor;
use App\Processors\HalfTimeFullTimeProcessor;
use App\Processors\MatchLines\MatchLinesProcessor;
use App\Processors\MatchMarkets\MatchMarketsProcessor;
use App\Processors\MatchResultProcessor;
use App\Processors\Score\BothTeamsToScoreInFirstHalfProcessor;
use App\Processors\Score\BothTeamsToScoreInSecondHalfProcessor;
use App\Processors\Score\BothTeamsToScoreProcessor;
use App\Processors\Score\CorrectScoreProcessor;
use App\Processors\SetBettingProcessor;
use App\Processors\TenMinutesResultProcessor;
use App\Processors\Tennis\ToWin\ToWinProcessor;
use App\Processors\ThreeWay\ThreeWayProcessor;
use App\Processors\ToWinMatchProcessor;
use App\Processors\WinningMarginProcessor;

return [
    'full_time_result' => MatchResultProcessor::class,
    'to_win_the_match' => MatchResultProcessor::class,
    'double_chance' => DoubleChanceProcessor::class,
    'draw_no_bet' => DrawNoBetProcessor::class,
    'winning_margin' => WinningMarginProcessor::class,

    'half_time_result' => HalfTimeResultProcessor::class,
    'half_time_double_chance' => HalfTimeDoubleChanceProcessor::class,
    'half_time_correct_score' => HalfTimeCorrectScoreProcessor::class,

    'goals_over_under' => GoalsOverUnderProcessor::class,
    'alternative_total_goals' => GoalsOverUnderProcessor::class,
    'first_half_goals' => FirstHalfGoalsOverUnderProcessor::class,
    'result_total_goals' => ResultTotalGoalsProcessor::class,

    'goals_odd_even' => GoalsOddEvenProcessor::class,
    '1st_half_goals_odd_even' => FirstHalfGoalsOddEvenProcessor::class,

    'both_teams_to_score' => BothTeamsToScoreProcessor::class,
    'both_teams_to_score_in_1st_half' => BothTeamsToScoreInFirstHalfProcessor::class,
    'both_teams_to_score_in_2nd_half' => BothTeamsToScoreInSecondHalfProcessor::class,
    'correct_score' => CorrectScoreProcessor::class,

    'half_time_full_time' => HalfTimeFullTimeProcessor::class,

    'asian_handicap' => AsianHandicapProcessor::class,

    '10_minute_result' => TenMinutesResultProcessor::class,

    'goalscorers' => GoalscorersProcessor::class,

    '3_way' => ThreeWayProcessor::class,

    'game_lines' => GameLinesProcessor::class,

    'game_betting_2_way' => GameBetting2WayProcessor::class,

    'match_markets' => MatchMarketsProcessor::class,

    'match_lines' => MatchLinesProcessor::class,

    'to_win_match' => ToWinMatchProcessor::class,

    'total_games_2_way' => GoalsOverUnderProcessor::class,

    'total_games_in_match' => TotalGamesInMatchProcessor::class,

    'to_win' => ToWinProcessor::class,

    'match_handicap_(games)' => MatchGamesHandicapProcessor::class,

    'set_betting' => SetBettingProcessor::class

    // 'specials' TBD and to be fixed, it's not being stored on the database

    // 'half_with_most_goals' => Don't have a data sample
    // 'last_team_to_score' => Don't have a data sample
    // 'home_team_exact_goals' => Don't have a data sample
    // 'away_team_exact_goals' => Don't have a data sample
];
