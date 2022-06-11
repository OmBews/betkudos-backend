<?php


namespace App\ValueObjects;


use App\Models\Events\Results\Result;
use Illuminate\Contracts\Support\Arrayable;

class Timer implements Arrayable
{
    public int|bool $isPlaying = false;
    public mixed $passedMinutes = 0;
    public mixed $kickOfTime = 0;
    public mixed $passedSeconds = 0;
    public mixed $currentTime = 0;
    public int $quarter = 0;
    public int $set = 0;
    public int $game = 0;

    public bool $countdown;

    private ?Result $result;

    public function __construct(?Result $result, bool $countdown = false)
    {
        $this->result = $result;
        $this->countdown = $countdown;

        if (! $this->result) {
            return;
        }

        $this->isPlaying = $this->result->is_playing;
        $this->currentTime = $this->result->current_time;
        $this->kickOfTime = $this->result->kick_of_time;
        $this->passedMinutes = $this->result->passed_minutes;
        $this->passedSeconds = $this->result->passed_seconds;
        $this->quarter = $this->result->quarter;
    }

    public function toArray(): array
    {
        return [
            'isPlaying' => $this->isPlaying,
            'currentTime' => $this->currentTime,
            'kickOfTime' => $this->kickOfTime,
            'passedMinutes' => $this->passedMinutes,
            'passedSeconds' => $this->passedSeconds,
            'countdown' => $this->countdown,
            'quarter' => $this->quarter,
            'set' => $this->set,
            'game' => $this->game,
        ];
    }

    public function setCurrentSet(int $set): self
    {
        $this->set = $set;

        return $this;
    }

    public function setGame(int $game): self
    {
        $this->game = $game;

        return $this;
    }
}
