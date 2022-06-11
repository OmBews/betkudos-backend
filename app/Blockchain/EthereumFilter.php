<?php

namespace App\Blockchain;

class EthereumFilter
{
    private $fromBlock, $toBlock, $address, $topics;

    function __construct($fromBlock, $toBlock, $address, $topics)
    {
        $this->fromBlock = $fromBlock;
        $this->toBlock = $toBlock;
        $this->address = $address;
        $this->topics = $topics;
    }

    function toArray()
    {
        return array(
            array
            (
                'fromBlock' => $this->fromBlock,
                'toBlock' => $this->toBlock,
                'address' => $this->address,
                'topics' => $this->topics
            )
        );
    }
}
