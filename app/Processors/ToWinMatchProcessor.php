<?php

namespace App\Processors;

class ToWinMatchProcessor extends MatchResultProcessor
{
    protected function scores(): array
    {
        return $this->setsResult();
    }

    protected function setsResult(): array
    {
        $homeTeamSetsWon = 0;
        $awayTeamSetsWon = 0;

        foreach ($this->sets() as $set) {
            [$homeScore, $awayScore] = explode('-', $set);

            if ($homeScore > $awayScore) {
                $advantage = $homeScore - $awayScore;

                if ($advantage >= $this->advantageNeededToWinASet()) {
                    $homeTeamSetsWon++;
                }
            } elseif ($awayScore > $homeScore) {
                $advantage = $awayScore - $homeScore;

                if ($advantage >= $this->advantageNeededToWinASet()) {
                    $awayTeamSetsWon++;
                }
            }
        }


        return [$homeTeamSetsWon, $awayTeamSetsWon];
    }

    protected function sets(): array
    {
        return explode(',', $this->result()->single_score);
    }

    protected function advantageNeededToWinASet(): int
    {
        return 2;
    }
}
