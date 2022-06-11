<?php

namespace Tests\Unit\Processors\AsianLines;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\AsianLines\AsianHandicapProcessor;

class FullGoalAsianHandicapProcessorTest extends AsianLineTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'asian_handicap')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return AsianHandicapProcessor::factory($selection, $market);
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider resultIsDraw
     */
    public function testWillVoidWhenTheSelectionIsDrawNoBetAndTheResultIsDraw(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_VOID);
    }

    public function resultIsDraw()
    {
        return [
            [['single_score' => '0-0'], ['name' => '0.0', 'header' => 'Home']],
            [['single_score' => '1-1'], ['name' => '0.0', 'header' => 'Home']],
            [['single_score' => '2-2'], ['name' => '0.0', 'header' => 'Home']],

            [['single_score' => '0-0'], ['name' => '0.0', 'header' => 'Away']],
            [['single_score' => '1-1'], ['name' => '0.0', 'header' => 'Away']],
            [['single_score' => '2-2'], ['name' => '0.0', 'header' => 'Away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeWins
     */
    public function testWillWonWhenTheSelectionIsHomeOnDrawNotBet(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function homeWins()
    {
        return [
            [['single_score' => '1-0'], ['name' => '0.0', 'header' => 'Home']],
            [['single_score' => '2-1'], ['name' => '0.0', 'header' => 'Home']],
            [['single_score' => '3-2'], ['name' => '0.0', 'header' => 'Home']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider homeLoses
     */
    public function testWillLoseWhenTheSelectionIsHomeOnDrawNotBetAndHomeTeamLoses(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function homeLoses()
    {
        return [
            [['single_score' => '0-1'], ['name' => '0.0', 'header' => 'Home']],
            [['single_score' => '1-2'], ['name' => '0.0', 'header' => 'Home']],
            [['single_score' => '2-3'], ['name' => '0.0', 'header' => 'Home']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayWins
     */
    public function testWillWonWhenTheSelectionIsAwayOnDrawNotBet(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_WON);
    }

    public function awayWins()
    {
        return [
            [['single_score' => '0-1'], ['name' => '0.0', 'header' => 'Away']],
            [['single_score' => '1-2'], ['name' => '0.0', 'header' => 'Away']],
            [['single_score' => '2-3'], ['name' => '0.0', 'header' => 'Away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     *
     * @dataProvider awayLoses
     */
    public function testWillLoseWhenTheSelectionIsAwayOnDrawNotBetAndAwayTeamLoses(array $result, array $odds)
    {
        return $this->runTestProcessor($result, $odds, BetSelection::STATUS_LOST);
    }

    public function awayLoses()
    {
        return [
            [['single_score' => '1-0'], ['name' => '0.0', 'header' => 'Away']],
            [['single_score' => '2-1'], ['name' => '0.0', 'header' => 'Away']],
            [['single_score' => '3-2'], ['name' => '0.0', 'header' => 'Away']],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param string $status
     *
     * @dataProvider underdogTeamResults
     */
    public function testUnderdogTeamResults(array $result, array $odds, string $status)
    {
        $this->runTestProcessor($result, $odds, $status);
    }

    public function underdogTeamResults()
    {
        return [
            // Home Team as Underdog
           [['single_score' => '0-0'], ['name' => '+1', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+1', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+1', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+1', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '0-2'], ['name' => '+1', 'header' => 'Home'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '2-0'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-2'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '0-3'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-4'], ['name' => '+2', 'header' => 'Home'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '2-0'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '3-0'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-2'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-3'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '0-4'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-5'], ['name' => '+3', 'header' => 'Home'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '2-0'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '3-0'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '4-0'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-2'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-3'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-4'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '0-5'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-6'], ['name' => '+4', 'header' => 'Home'], BetSelection::STATUS_LOST],

            // Away Team as Underdog

           [['single_score' => '0-0'], ['name' => '+1', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+1', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+1', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+1', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '2-0'], ['name' => '+1', 'header' => 'Away'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-2'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '2-0'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '3-0'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '4-0'], ['name' => '+2', 'header' => 'Away'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-2'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-3'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '2-0'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '3-0'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '4-0'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '5-0'], ['name' => '+3', 'header' => 'Away'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-1'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-2'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-3'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-4'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '2-0'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '3-0'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '4-0'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '5-0'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '6-0'], ['name' => '+4', 'header' => 'Away'], BetSelection::STATUS_LOST],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param string $status
     *
     * @dataProvider preferredTeamResults
     */
    public function testPreferredTeamResults(array $result, array $odds, string $status)
    {
        $this->runTestProcessor($result, $odds, $status);
    }

    public function preferredTeamResults()
    {
        return [
            // Home Team as Preferred
           [['single_score' => '0-0'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-0'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '2-0'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '3-0'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '3-1'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-2'], ['name' => '-1', 'header' => 'Home'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-0'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '2-0'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '3-0'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '4-0'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '4-1'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-2'], ['name' => '-2', 'header' => 'Home'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-0'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '2-0'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '3-0'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '4-0'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '5-1'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '6-2'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-2'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-3'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-4'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-5'], ['name' => '-3', 'header' => 'Home'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '1-0'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '2-0'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '3-0'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '4-0'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_VOID],
           [['single_score' => '5-0'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '6-1'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '7-2'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_WON],
           [['single_score' => '0-1'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-2'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-3'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-4'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-5'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],
           [['single_score' => '0-6'], ['name' => '-4', 'header' => 'Home'], BetSelection::STATUS_LOST],

            // Away Team as Preferred

           [['single_score' => '0-0'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-1'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '0-2'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-3'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-3'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '2-0'], ['name' => '-1', 'header' => 'Away'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-1'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-2'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '0-3'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-4'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-4'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '2-0'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '3-0'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '4-0'], ['name' => '-2', 'header' => 'Away'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-1'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-2'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-3'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '0-4'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-5'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-5'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '2-0'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '3-0'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '4-0'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '5-0'], ['name' => '-3', 'header' => 'Away'], BetSelection::STATUS_LOST],

           [['single_score' => '0-0'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '1-1'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-1'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-2'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-3'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '0-4'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_VOID],
           [['single_score' => '0-5'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '0-6'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-6'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_WON],
           [['single_score' => '1-0'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '2-0'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '3-0'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '4-0'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '5-0'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
           [['single_score' => '6-0'], ['name' => '-4', 'header' => 'Away'], BetSelection::STATUS_LOST],
        ];
    }
}
