<?php

namespace App\Contracts\Processors\AsianLines;

interface AsianLine
{
    public function __construct(AsianHandicapProcessorInterface $processor, string $handicap);

    /**
     * @return string
     * @throws \Throwable
     */
    public function status(): string;

    public function determineUnderdogTeamStatus(): string;

    public function determinePreferredTeamStatus(): string;
}
