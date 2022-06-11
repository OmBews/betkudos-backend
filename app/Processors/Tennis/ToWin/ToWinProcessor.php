<?php


namespace App\Processors\Tennis\ToWin;


use App\Contracts\Processors\HybridMarketSelectionProcessor;

class ToWinProcessor extends HybridMarketSelectionProcessor
{
    protected function processors(): array
    {
        return [
            'Match' => ToWinLiveMatchProcessor::class
        ];
    }
}
