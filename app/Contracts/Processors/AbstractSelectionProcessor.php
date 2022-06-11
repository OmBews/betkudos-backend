<?php

namespace App\Contracts\Processors;

use App\Models\Bets\Selections\BetSelection;
use App\Models\Markets\Market;
use App\Models\Events\Results\Result;

abstract class AbstractSelectionProcessor
{
    public const SELECTION_RESULT_DRAW = 'draw';
    public const SELECTION_RESULT_WON = 'won';
    public const SELECTION_RESULT_LOST = 'lost';

    /**
     * @var BetSelection
     */
    protected $selection;

    /**
     * @var Market
     */
    protected $market;

    public function __construct(BetSelection $selection, Market $market)
    {
        $this->selection = $selection;
        $this->market = $market;
    }

    /**
     * @param BetSelection $selection
     * @param Market $market
     * @return static
     */
    public static function factory(BetSelection $selection, Market $market): AbstractSelectionProcessor
    {
        return new static($selection, $market);
    }

    /**
     * Determine the selection status based on the match results
     * @return string
     */
    abstract public function process(): string;

    protected function selectionName()
    {
        return $this->selection->name;
    }

    /**
     * @return Result
     */
    protected function result()
    {
        return $this->selection->match->result;
    }

    protected function isScoresAvailable(): bool
    {
        return $this->result()->single_score !== null;
    }

    protected function scores(): array
    {
        return explode('-', $this->result()->single_score);
    }

    protected function firstHalfScores(): array
    {
        $scores = json_decode($this->result()->scores);

        return [$scores->{1}->home, $scores->{1}->away];
    }

    protected function secondHalfScores(): array
    {
        $scores = json_decode($this->result()->scores);

        return [$scores->{2}->home, $scores->{2}->away];
    }

    protected function drawSelectionNames(): array
    {
        return ['Draw', 'draw', 'X', 'x', 'Tie', 'tie'];
    }

    protected function homeSelectionNames(): array
    {
        $homeTeamName = $this->selection->match->home->name;

        return ['1', $homeTeamName, strtolower($homeTeamName)];
    }

    protected function awaySelectionNames(): array
    {
        $awayTeamName = $this->selection->match->away->name;

        return ['2', $awayTeamName, strtolower($awayTeamName)];
    }

    protected function hasEndedInDraw()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $homeTeamScore === $awayTeamScore;
    }

    protected function homeTeamWon()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $homeTeamScore > $awayTeamScore;
    }

    protected function awayTeamWon()
    {
        [$homeTeamScore, $awayTeamScore] = $this->scores();

        return $awayTeamScore > $homeTeamScore;
    }

    /**
     * @return string
     * @throws \Exception
     */
    protected function getSelectionResult(): string
    {
        if ($this->hasEndedInDraw()) {
            return self::SELECTION_RESULT_DRAW;
        }

        if ($this->isSelectionHomeTeam()) {
            if ($this->homeTeamWon()) {
                return self::SELECTION_RESULT_WON;
            }

            return self::SELECTION_RESULT_LOST;
        } elseif ($this->isSelectionAwayTeam()) {
            if ($this->awayTeamWon()) {
                return self::SELECTION_RESULT_WON;
            }

            return self::SELECTION_RESULT_LOST;
        }

        throw new \Exception("Can't determine selection result");
    }

    protected function isSelectionHomeTeam()
    {
        return in_array($this->selectionName(), $this->homeSelectionNames());
    }

    protected function isSelectionAwayTeam()
    {
        return in_array($this->selectionName(), $this->awaySelectionNames());
    }

    protected function isSelectionDraw()
    {
        return in_array($this->selectionName(), $this->drawSelectionNames());
    }
}
