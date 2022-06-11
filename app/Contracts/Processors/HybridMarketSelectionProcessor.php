<?php

namespace App\Contracts\Processors;

use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;

abstract class HybridMarketSelectionProcessor extends AbstractSelectionProcessor
{
    private $processor;

    /**
     * HybridMarketSelectionProcessor constructor.
     * @param BetSelection $selection
     * @param Market $market
     * @throws \Exception
     */
    public function __construct(BetSelection $selection, Market $market)
    {
        parent::__construct($selection, $market);

        $this->processor = $this->getProcessor();
    }

    public function process(): string
    {
        return $this->processor->process();
    }

    /**
     * @return AbstractSelectionProcessor
     * @throws \Exception
     */
    private function getProcessor(): AbstractSelectionProcessor
    {
        $processors = $this->processors();

        if (array_key_exists($this->selectionName(), $processors)) {
            return new $processors[$this->selectionName()]($this->selection, $this->market);
        }

        throw new \Exception("Can't find a processor or the processor is not implemented for this market.");
    }

    abstract protected function processors(): array;
}
