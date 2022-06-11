<?php

namespace Tests\Unit\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\GoalscorersProcessor;

class GoalscorersProcessorTest extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'goalscorers')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return GoalscorersProcessor::factory($selection, $market);
    }

    protected function runCustomTestProcessor(array $result, array $odds, array $stats, string $expectedStatus)
    {
        [$selection, $market, , , $match] = $this->buildSelection($result, $odds, $this->market());

        $match->stats()->updateOrCreate(['match_id' => $match->getKey()], $stats);

        $processor = $this->processor($selection, $market);

        $this->assertEquals($expectedStatus, $processor->process());
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider firstGoalsSelectionsWon
     */
    public function testWillWonWhenTheSelectionAndResultIsTheGoalscorerToScoreTheFirstGoal(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_WON);
    }

    public function firstGoalsSelectionsWon()
    {
        return [
            [
                ['single_score' => '1-0'],
                ['name' => 'Lionel Messi', 'header' => 'First'],
                ['events' => '[{"text":"1st Goal - Lionel Messi"}]', 'stats' => []]
            ],
            [
                ['single_score' => '1-0'],
                ['name' => 'C. Ronaldo', 'header' => 'First'],
                ['events' => '[{"text":"1st Goal - C. Ronaldo"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider firstGoalsSelectionsLose
     */
    public function testWillLoseWhenTheSelectionIsTheGoalscorerToScoreTheFirstGoalButTheResultIsDifferent(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_LOST);
    }

    public function firstGoalsSelectionsLose()
    {
        return [
            [
                ['single_score' => '1-0'],
                ['name' => 'Lionel Messi', 'header' => 'First'],
                ['events' => '[{"text":"1st Goal - C. Ronaldo"}]', 'stats' => []]
            ],
            [
                ['single_score' => '1-0'],
                ['name' => 'C. Ronaldo', 'header' => 'First'],
                ['events' => '[{"text":"1st Goal - Lionel Messi"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider lastGoalsSelectionsWon
     */
    public function testWillWonWhenTheSelectionAndResultIsTheGoalscorerToScoreTheLastGoal(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_WON);
    }

    public function lastGoalsSelectionsWon()
    {
        return [
            [
                ['single_score' => '3-0'],
                ['name' => 'Lionel Messi', 'header' => 'Last'],
                ['events' => '[{"text":"3rd Goal - Lionel Messi"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-0'],
                ['name' => 'C. Ronaldo', 'header' => 'Last'],
                ['events' => '[{"text":"2nd Goal - C. Ronaldo"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider lastGoalsSelectionsLose
     */
    public function testWillLoseWhenTheSelectionIsTheGoalscorerToScoreTheLastGoalButTheResultIsDifferent(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_LOST);
    }

    public function lastGoalsSelectionsLose()
    {
        return [
            [
                ['single_score' => '2-0'],
                ['name' => 'Lionel Messi', 'header' => 'Last'],
                ['events' => '[{"text":"2nd Goal - C. Ronaldo"}]', 'stats' => []]
            ],
            [
                ['single_score' => '3-0'],
                ['name' => 'C. Ronaldo', 'header' => 'Last'],
                ['events' => '[{"text":"3rd Goal - Lionel Messi"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider toScoreAnyTimeWillWin
     */
    public function testWillWonWhenTheSelectionAndResultIsTheGoalscorerToScoreAtAnytime(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_WON);
    }

    public function toScoreAnyTimeWillWin()
    {
        return [
            [
                ['single_score' => '3-1'],
                ['name' => 'Lionel Messi', 'header' => 'Anytime'],
                ['events' => '[{"text":"1st Goal - C. Ronaldo"},{"text":"4th Goal - Lionel Messi"}]', 'stats' => []]
            ],
            [
                ['single_score' => '2-0'],
                ['name' => 'C. Ronaldo', 'header' => 'Anytime'],
                ['events' => '[{"text":"1st Goal - C. Ronaldo"}, {"text":"2nd Goal - Lionel Messi"}]', 'stats' => []]
            ],
        ];
    }

    /**
     * @param array $result
     * @param array $odds
     * @param array $stats
     *
     * @dataProvider toScoreAnyTimeWillLose
     */
    public function testWillLoseWhenTheSelectionIsTheGoalscorerToScoreAtAnytimeButTheResultIsDifferent(array $result, array $odds, array $stats)
    {
        $this->runCustomTestProcessor($result, $odds, $stats, BetSelection::STATUS_LOST);
    }

    public function toScoreAnyTimeWillLose()
    {
        return [
            [
                ['single_score' => '1-0'],
                ['name' => 'Lionel Messi', 'header' => 'Anytime'],
                ['events' => '[{"text":"2nd Goal - P. Coutinho"}]', 'stats' => []]
            ],
            [
                ['single_score' => '1-0'],
                ['name' => 'C. Ronaldo', 'header' => 'Anytime'],
                ['events' => '[{"text":"1st Goal - Neymar Jr"}]', 'stats' => []]
            ],
        ];
    }
}
