<?php

namespace App\Processors\AsianLines;

class MatchGamesHandicapProcessor extends AsianHandicapProcessor
{
    public function scores(): array
    {
        return $this->games();
    }

    protected function games(): array
    {
        $homeTeamGames = 0;
        $awayTeamGames = 0;

        foreach ($this->sets() as $set) {
            [$homeTeamScore, $awayTeamScore] = explode('-', $set);

            $homeTeamGames += $homeTeamScore;
            $awayTeamGames += $awayTeamScore;
        }

        return [$homeTeamGames, $awayTeamGames];
    }

    protected function sets(): array
    {
        return explode(',', $this->result()->single_score);
    }
}
