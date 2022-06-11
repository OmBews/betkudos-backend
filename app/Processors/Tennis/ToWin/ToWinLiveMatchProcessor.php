<?php


namespace App\Processors\Tennis\ToWin;


use App\Processors\ToWinMatchProcessor;

class ToWinLiveMatchProcessor extends ToWinMatchProcessor
{
    protected function selectionName(): string
    {
        return $this->selection->header;
    }
}
