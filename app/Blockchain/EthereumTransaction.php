<?php

namespace App\Blockchain;

class EthereumTransaction
{

    private $to, $from, $gas, $gasPrice, $value, $data, $nonce;


//  "SendTransaction": {
//  "__required": ["from", "data"],
//  "from": "D20",
//  "to": "D20",
//  "gas": "Q",
//  "gasPrice": "Q",
//  "value": "Q",
//  "data": "D",
//  "nonce": "Q"

    // TODO RETHINK PROPPER DEFAULT VALUES !!!

    function __construct(
        $to_address,
        $data = NULL,
        $from = NULL,
        $gas = NULL,
        $gasPrice = NULL,
        $value = NULL,
        $nonce = NULL
    )
    {

        $this->to = $to_address;
        $this->from = $from;
        $this->gas = $gas;
        $this->gasPrice = $gasPrice;
        $this->value = $value;
        $this->data = $data;
        $this->nonce = $nonce;
    }

    function toArray()
    {
        return array(
            array(
                'from' => $this->from,
                'to' => $this->to,
                'gas' => $this->gas,
                'gasPrice' => $this->gasPrice,
                'value' => $this->value,
                'data' => $this->data,
                'nonce' => $this->nonce
            )
        );
    }

    function setArgument($method, $value)
    {

        if (strlen($method) != 10) {
            throw new \InvalidArgumentException($method . ' should be a "0x" + 8 chars.');
        }
        if (!ctype_xdigit($value) || strlen($value) !== 32) {
//        throw new \InvalidArgumentException($value . ' should be 16 char Hex encoded (32 chars).');
        }

        $this->data = $method . $value;
    }
}
