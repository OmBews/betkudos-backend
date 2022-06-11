<?php


namespace App\Contracts\BetsAPI\Bet365;


use Illuminate\Support\Collection;

interface InPlayDataCompilerInterface
{
    public const MARKET_GROUP_DATA_TYPE = 'MG';
    public const MARKET_DATA_TYPE = 'MA';
    public const PARTICIPANT_DATA_TYPE = 'PA';

    public const MARKET_DATA_TYPES = [
        self::MARKET_GROUP_DATA_TYPE,
        self::MARKET_DATA_TYPE,
        self::PARTICIPANT_DATA_TYPE,
    ];

    public function __construct(object $data);

    public function compile(callable $transform): InPlayDataCompilerInterface;

    public function get(): Collection;

    public function first();

    public function toArray(): array;
}
