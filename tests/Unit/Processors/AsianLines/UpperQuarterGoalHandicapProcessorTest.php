<?php

namespace Tests\Unit\Processors\AsianLines;


use App\Models\Bets\Selections\BetSelection;

class UpperQuarterGoalHandicapProcessorTest extends AsianLineTestCase
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
            [['single_score' => '0-0'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-2'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-3'], ['name' => '+0.75', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-2'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-3'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-3'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-4'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-4'], ['name' => '+1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-3'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-3'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-4'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-4'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '0-5'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-5'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-5'], ['name' => '+2.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],

            [['single_score' => '0-0'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-3'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '1-3'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-4'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '1-4'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-5'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-5'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-5'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-6'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-6'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-6'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '3-6'], ['name' => '+3.75', 'header' => 'Home'], BetSelection::STATUS_WON],

            // Away Team as underdog
            [['single_score' => '0-0'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-0'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '3-0'], ['name' => '+0.75', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '2-1'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '3-0'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '3-1'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '4-0'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '4-1'], ['name' => '+1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '3-0'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '3-1'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '4-0'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '4-1'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '5-0'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '5-1'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '5-2'], ['name' => '+2.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],

            [['single_score' => '0-0'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-1'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '0-2'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-0'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '3-0'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '3-1'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '4-0'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '4-1'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '5-0'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '5-1'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '5-2'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '6-0'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '6-1'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '6-2'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_HALF_LOST],
            [['single_score' => '6-3'], ['name' => '+3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
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
            [['single_score' => '0-0'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-1'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '2-1'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '3-0'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '3-1'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '0-1'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-0.75', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '2-1'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '3-0'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '3-1'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '4-0'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '4-1'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '4-2'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '4-3'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-1.75', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '3-0'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '3-1'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '4-0'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '4-1'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '4-2'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '4-3'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '5-0'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '5-1'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '5-2'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '5-3'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '5-4'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-2.75', 'header' => 'Home'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '3-0'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '3-1'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '4-0'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '4-1'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '4-2'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '4-3'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '5-0'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '5-1'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '5-2'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '5-3'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '5-4'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '6-0'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '6-1'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_WON],
            [['single_score' => '6-2'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '6-3'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '6-4'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '6-5'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-3.75', 'header' => 'Home'], BetSelection::STATUS_LOST],

            // Away Team as preferred
            [['single_score' => '0-0'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-1'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-2'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '0-3'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-3'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-0'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-0.75', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-2'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-3'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-3'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '0-4'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-4'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-4'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '3-4'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-1.75', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-3'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-3'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-4'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-4'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '2-4'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '3-4'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-5'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-5'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-5'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '3-5'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '4-5'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-2.75', 'header' => 'Away'], BetSelection::STATUS_LOST],

            [['single_score' => '0-0'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-1'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-1'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-2'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-2'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-3'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-3'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-4'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '1-4'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-4'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '3-4'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-5'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-5'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '2-5'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '3-5'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '4-5'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '0-6'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '1-6'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_WON],
            [['single_score' => '2-6'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_HALF_WON],
            [['single_score' => '3-6'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '4-6'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '5-6'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '1-0'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-0'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
            [['single_score' => '2-1'], ['name' => '-3.75', 'header' => 'Away'], BetSelection::STATUS_LOST],
        ];
    }
}
