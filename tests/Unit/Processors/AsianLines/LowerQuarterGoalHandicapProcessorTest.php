<?php

namespace Tests\Unit\Processors\AsianLines;

use App\Models\Bets\Selections\BetSelection;


class LowerQuarterGoalHandicapProcessorTest extends AsianLineTestCase
{
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
            // Home Team as underdog
            [['single_score' => '0-0'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-1'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-0'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '+0.25', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '0-2'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '0-3'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-3'], ['name' => '+1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-2'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-3'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-3'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-4'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-4'], ['name' => '+2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-3'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-3'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-4'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '0-4'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-5'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-5'], ['name' => '+3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],


            // Away Team as underdog
            [['single_score' => '0-0'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-1'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '0-1'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '+0.25', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-0'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '2-0'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '3-0'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '3-1'], ['name' => '+1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-2'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '3-0'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '3-1'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '4-1'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '4-0'], ['name' => '+2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '3-0'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '3-1'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '4-1'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '4-0'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '5-0'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '5-1'], ['name' => '+3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
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
            // Home Team as preferred
            [['single_score' => '0-0'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-0'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-0'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-2'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-1'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-0.25', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-1'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-2'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '3-0'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '3-1'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-1.25', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-1'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-2'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '3-0'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '3-1'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '4-0'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '4-1'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '4-2'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-1'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-2.25', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-2'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '3-0'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '3-1'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '4-0'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '4-1'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '4-2'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '5-0'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '5-1'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '5-2'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '5-3'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-3.25', 'header' => 'Home'], BetSelection::STATUS_LOST],

            // Away Team as preferred
            [['single_score' => '0-0'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-1'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-2'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-2'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-0'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-0.25', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-1'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-2'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-3'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-3'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-1.25', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-2'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-2'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-3'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-3'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-4'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-4'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-4'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-0'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-2.25', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-2'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-3'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-3'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-4'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-4'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-4'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-5'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-5'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-5'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '3-5'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-3.25', 'header' => 'Away'], BetSelection::STATUS_LOST],
        ];
    }
}
