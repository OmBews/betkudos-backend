<?php

namespace App\Http\Clients\BetsAPI\Responses\Bet365;

use App\Exceptions\BetsAPI\APICallException;
use App\Http\Clients\BetsAPI\Responses\BetsAPIResponse;
use Illuminate\Support\Collection;

class ResultResponse extends BetsAPIResponse
{
    /**
     * @var int
     */
    private $eventId;

    /**
     * @var int
     */
    private $fixtureId;

    /**
     * @var int
     */
    private $matchId;

    /**
     * @var int
     */
    private $time_status;

    /**
     * @var int
     */
    private $time;

    /**
     * @var string|null
     */
    private $singleScore;

    /**
     * @var mixed
     */
    private $scores;

    /**
     * @var \stdClass
     */
    private $away;

    /**
     * @var \stdClass
     */
    private $home;

    /**
     * @var \stdClass
     */
    private $league;

    /**
     * @var array|mixed
     */
    private $events;

    /**
     * @var \stdClass|mixed
     */
    private $stats;

    /**
     * ResultResponse constructor.
     * @param string $content
     * @param int $fixtureId
     * @param int $matchId
     * @throws APICallException
     */
    public function __construct(string $content, int $fixtureId, int $matchId)
    {
        parent::__construct($content);

        $this->results = Collection::make($this->results);

        if (is_null($result = $this->results->first()) || (isset($result->success) && ! $result->success)) {
            throw new APICallException(APICallException::UNAVAILABLE_MATCH_RESULT);
        }

        $result = $this->results->first();

        $this->fixtureId = $fixtureId;
        $this->eventId = $result->id;
        $this->matchId = $matchId;
        $this->time_status = $result->time_status;
        $this->time = $result->time;
        $this->singleScore = $result->ss ?? null;
        $this->scores = isset($result->scores) ? $result->scores : new \stdClass();
        $this->away = $result->away;
        $this->home = $result->home;
        $this->league = $result->league;
        $this->events = $result->events ?? [];
        $this->stats = $result->stats ?? [];
    }

    /**
     * @param string $content
     * @param int $fixtureId
     * @param int $matchId
     * @return static
     * @throws APICallException
     */
    public static function factory(string $content, int $fixtureId, int $matchId)
    {
        return new static($content, $fixtureId, $matchId);
    }

    /**
     * @return int
     */
    public function getFixtureId(): int
    {
        return $this->fixtureId;
    }

    /**
     * @return int
     */
    public function getMatchId(): int
    {
        return $this->matchId;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @return int
     */
    public function getTimeStatus(): int
    {
        return $this->time_status;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return string|null
     */
    public function getSingleScore(): ?string
    {
        return $this->singleScore;
    }

    /**
     * @return mixed
     */
    public function getScores()
    {
        return $this->scores;
    }

    /**
     * @return \stdClass
     */
    public function getAway(): \stdClass
    {
        return $this->away;
    }

    /**
     * @return \stdClass
     */
    public function getHome(): \stdClass
    {
        return $this->home;
    }

    /**
     * @return \stdClass
     */
    public function getLeague(): \stdClass
    {
        return $this->league;
    }

    /**
     * @return array|mixed
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @return array|mixed
     */
    public function getStats()
    {
        return $this->stats;
    }
}
