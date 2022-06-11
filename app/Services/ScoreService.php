<?php


namespace App\Services;


use App\Contracts\Services\ScoreServiceInterface;
use Illuminate\Support\Arr;

class ScoreService implements ScoreServiceInterface
{
    public function __construct(private string $score){}

    public static function factory(string $score): ScoreServiceInterface
    {
        return new static($score);
    }

    public function getScore(): string
    {
        return $this->score;
    }

    public function scores(): array
    {
        return $this->toScore($this->score);
    }

    public function sets(): array
    {
        return $this->toSet($this->score);
    }

    public function overallSetScore(bool $isPlaying = false): array
    {
        $homeTeamSetsWon = 0;
        $awayTeamSetsWon = 0;

        $sets = $this->sets();

        if ($isPlaying) {
            array_pop($sets);
        }

        if (is_array($sets)) {
            foreach ($sets as $set) {
                [$homeScore, $awayScore] = $this->toScore($set);

                if ($homeScore > $awayScore) {
                    $advantage = $homeScore - $awayScore;

                    if ($advantage >= self::SET_POINTS_ADVANTAGE_TO_WIN || $homeScore >= 7) {
                        $homeTeamSetsWon++;
                    }
                } elseif ($awayScore > $homeScore) {
                    $advantage = $awayScore - $homeScore;

                    if ($advantage >= self::SET_POINTS_ADVANTAGE_TO_WIN || $awayScore >= 7) {
                        $awayTeamSetsWon++;
                    }
                }
            }
        }

        return [$homeTeamSetsWon, $awayTeamSetsWon];
    }

    public function currentSetScore(): array
    {
        if ( $this->lastSet() ) {
            return $this->toScore($this->lastSet());
        }

        return [0, 0];
    }

    private function lastSet(): ?string
    {
        return Arr::last($this->sets());
    }

    private function toScore(string $score): array
    {
        return explode(self::SCORE_SEPARATOR, $score);
    }

    private function toSet(string $score): array
    {
        return explode(self::SET_SEPARATOR, $score);
    }

    public function isSetBasedScore(): bool
    {
        return str_contains($this->score, self::SET_SEPARATOR);
    }
}
