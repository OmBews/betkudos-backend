<?php

namespace Tests\Unit\Processors\Tennis;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Processors\MatchLines\MatchLinesProcessor;
use App\Processors\Tennis\ToWin\ToWinLiveMatchProcessor;
use App\Processors\Tennis\ToWin\ToWinProcessor;
use PHPUnit\Framework\TestCase;
use Tests\Unit\Processors\ProcessorTestCase;
use Tests\Unit\Processors\ToWinMatchProcessorTest;

class ToWinProcessorTest extends ToWinMatchProcessorTest
{
    protected static $selectionNameKey = 'header';

    protected array $defaultOdds = ['header' => 'Match'];

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function market(): Market
    {
        return Market::where('key', 'to_win')->first();
    }

    protected function processor(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return ToWinLiveMatchProcessor::factory($selection, $market);
    }
}
