<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\MatchResultProcessor;

class MatchResultProcessorTest extends ProcessorTestCase
{
    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return MatchResultProcessor::factory($selection, $market);
    }

    protected function market(): Market
    {
        return (new Market())->fullTimeResult();
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawWonProvider
     */
    public function testWillWonWhenTheSelectionAndTheResultIsDraw(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function drawWonProvider()
    {
        return [
            [['single_score' => '0-0'], [static::$selectionNameKey => 'Draw']],
            [['single_score' => '0-0'], [static::$selectionNameKey => 'draw']],
            [['single_score' => '0-0'], [static::$selectionNameKey => 'Tie']],
            [['single_score' => '0-0'], [static::$selectionNameKey => 'tie']],
            [['single_score' => '0-0'], [static::$selectionNameKey => 'X']],
            [['single_score' => '0-0'], [static::$selectionNameKey => 'x']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider drawLossProvider
     */
    public function testWillLossWhenTheSelectionIsDrawButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function drawLossProvider()
    {
        return [
            [['single_score' => '1-0'], [static::$selectionNameKey => 'Draw']],
            [['single_score' => '0-1'], [static::$selectionNameKey => 'draw']],
            [['single_score' => '1-0'], [static::$selectionNameKey => 'Tie']],
            [['single_score' => '0-1'], [static::$selectionNameKey => 'tie']],
            [['single_score' => '2-1'], [static::$selectionNameKey => 'X']],
            [['single_score' => '0-2'], [static::$selectionNameKey => 'x']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamWonProvider
     */
    public function testWillWonWhenTheSelectionAndTheResultIsHomeTeam(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeTeamWonProvider()
    {
        $homeTeamScore = rand(1, 10);
        $awayTeamScore = $homeTeamScore - 1;

        return [
            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => '1']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'Home']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'home']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeTeamLossProvider
     */
    public function testWillLossWhenTheSelectionIsHomeTeamButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeTeamLossProvider()
    {
        $homeTeamScore = rand(1, 10);
        $awayTeamScore = $homeTeamScore + 1;

        return [
            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => '1']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'Home']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'home']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamWonProvider
     */
    public function testWillWonWhenTheSelectionAndTheResultIsAwayTeam(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayTeamWonProvider()
    {
        $homeTeamScore = rand(1, 10);
        $awayTeamScore = $homeTeamScore + 1;

        return [
            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => '2']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'Away']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayTeamLossProvider
     */
    public function testWillLossWhenTheSelectionAwayTeamButTheResultIsDifferent(array $result, array $odds)
    {
        $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayTeamLossProvider()
    {
        $homeTeamScore = rand(1, 10);
        $awayTeamScore = $homeTeamScore - 1;

        return [
            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => '2']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'Away']],

            [['single_score' => "$homeTeamScore-$awayTeamScore"], [static::$selectionNameKey => 'away']],
        ];
    }
}
