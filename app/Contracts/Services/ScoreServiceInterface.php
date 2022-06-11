<?php


namespace App\Contracts\Services;


interface ScoreServiceInterface
{
    public const SET_SEPARATOR = ',';

    public const SCORE_SEPARATOR = '-';

    public const SET_POINTS_ADVANTAGE_TO_WIN = 2;

    public function __construct(string $score);

    public static function factory(string $score): ScoreServiceInterface;

    public function getScore(): string;

    public function scores(): array;

    public function sets(): array;

    public function overallSetScore(bool $isPlaying = false): array;

    public function currentSetScore(): array;

    public function isSetBasedScore(): bool;
}
