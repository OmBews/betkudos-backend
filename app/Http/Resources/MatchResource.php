<?php

namespace App\Http\Resources;

use App\Contracts\Repositories\OddsRepository;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use App\Services\MatchEventsService;
use App\Services\ScoreService;
use App\ValueObjects\Timer;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class MatchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function toArray($request)
    {
        $scoreService = (int) $this->time_status === Event::STATUS_IN_PLAY && $this->result && $this->result->single_score ?
            ScoreService::factory($this->result->single_score) :
            null;

        $isLive = (int) $this->time_status === Event::STATUS_IN_PLAY;

        $homeTeamLogo = $this->home->image_id ?? 0;
        $awayTeamLogo = $this->away->image_id ?? 0;

        return [
            'id' => $this->id,
            'name' => $this->home->name . ' v ' . $this->away->name,
            'homeTeamName' => $this->home->name,
            'awayTeamName' => $this->away->name,
            'homeTeamLogo' => $homeTeamLogo ? "https://assets.b365api.com/images/team/b/{$homeTeamLogo}.png" : null,
            'awayTeamLogo' => $awayTeamLogo ? "https://assets.b365api.com/images/team/b/{$awayTeamLogo}.png" : null,
            'starts_at' => gmdate("Y-m-d\TH:i:s\Z", $this->starts_at),
            'ended' => $this->when(
                $this->time_status === Event::STATUS_ENDED,
                true
            ),
            'is_live' => $isLive,
            'setBasedScore' => $this->when($isLive, Sport::hasSetBasedScore($this->sport_id)),
            'homeTeamScore' => $this->when($isLive && $this->result, function () use ($scoreService) {
                    if (!$this->result->single_score) {
                        return '-';
                    }

                    if (Sport::hasSetBasedScore($this->sport_id)) {
                        [$homeTeamScore, $awayTeamScore] = $scoreService->overallSetScore(true);
                    } else {
                        [$homeTeamScore, $awayTeamScore] =  $scoreService->scores();
                    }

                    return $homeTeamScore ?? 0;
                }
            ),
            'awayTeamScore' => $this->when($isLive && $this->result, function () use ($scoreService) {
                    if (!$this->result->single_score) {
                        return '-';
                    }

                    if (Sport::hasSetBasedScore($this->sport_id)) {
                        [$homeTeamScore, $awayTeamScore] = $scoreService->overallSetScore(true);
                    } else {
                        [$homeTeamScore, $awayTeamScore] =  $scoreService->scores();
                    }

                    return $awayTeamScore ?? 0;
                }
            ),
            'currentSetScore' => $this->when(
                $isLive && Sport::hasSetBasedScore($this->sport_id),
                function () use ($scoreService) {
                    return $scoreService->currentSetScore();
                }
            ),
            'points' => $this->when(
                $isLive && Sport::hasSetBasedScore($this->sport_id),
                function () {
                    if ($this->result->points) {
                        return ScoreService::factory($this->result->points)->scores();
                    }

                    return [0,0];
                }
            ),
            'markets' => $this->markets,
            'timer' => $this->when($isLive && $this->result, function () use ($scoreService) {
                $timer = new Timer($this->result, Sport::hasCountdownTimer($this->sport_id));

                if (Sport::hasSetBasedScore($this->sport_id)) {
                    $game = MatchEventsService::factory($this->stats->events)->currentGame();

                    $timer->setCurrentSet(count($scoreService->sets()))
                        ->setGame($game);
                }

                return $timer->toArray();
            }),
        ];
    }
}
