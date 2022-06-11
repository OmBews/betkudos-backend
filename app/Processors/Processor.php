<?php

namespace App\Processors;

use App\Contracts\Processors\AbstractSelectionProcessor;
use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;

class Processor extends AbstractSelectionProcessor
{
    private $processors;

    public function __construct(BetSelection $selection, Market $market)
    {
        parent::__construct($selection, $market);

        $this->processors = config('processors');
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    public function process(): string
    {
        if (! $this->isScoresAvailable()) {
            return BetSelection::STATUS_OPEN;
        }

        $processor = $this->getProcessor();

        if (is_null($processor)) {
            return BetSelection::STATUS_OPEN;
        }

        return $processor->process();
    }

    /**
     * @return ?AbstractSelectionProcessor
     * @throws \Throwable
     */
    public function getProcessor(): ?AbstractSelectionProcessor
    {
        if (! array_key_exists($this->market->key, $this->processors)) {
            return null;
        }

        $processorClass = $this->processors[$this->market->key];
        return new $processorClass($this->selection, $this->market);
    }
}
