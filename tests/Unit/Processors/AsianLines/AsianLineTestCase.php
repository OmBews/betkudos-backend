<?php


namespace Tests\Unit\Processors\AsianLines;


use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\AsianLines\AsianHandicapProcessor;
use Tests\Unit\Processors\ProcessorTestCase;

abstract class AsianLineTestCase extends ProcessorTestCase
{
    protected function market(): Market
    {
        return Market::where('key', 'asian_handicap')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return AsianHandicapProcessor::factory($selection, $market);
    }

    public abstract function testUnderdogTeamResults(array $result, array $odds, string $status);

    public abstract function testPreferredTeamResults(array $result, array $odds, string $status);
}
