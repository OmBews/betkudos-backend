<?php

namespace App\Http\Clients\BetsAPI\Responses\Bet365\Entities;

class UpcomingMatch
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var int
     */
    private $sport_id;

    /**
     * @var int
     */
    private $time;

    /**
     * @var int
     */
    private $time_status;

    /**
     * @var \stdClass
     */
    private $league;

    /**
     * @var \stdClass
     */
    private $home;

    /**
     * @var \stdClass
     */
    private $away;

    /**
     * @var string
     */
    private $ss;

    /**
     * @var int
     */
    private $our_event_id;

    /**
     * @var string
     */
    private $updated_at;

    private const FIELDS = [
        'id', 'sport_id', 'time',
        'time_status', 'league', 'home',
        'away', 'our_event_id', 'updated_at',
    ];

    /**
     * UpcomingMatch constructor.
     * @param \stdClass $match
     * @throws \Exception
     */
    public function __construct(\stdClass $match)
    {
        $this->validate($match);

        $this->id = $match->id;
        $this->sport_id = $match->sport_id;
        $this->time = $match->time;
        $this->time_status = $match->time_status;
        $this->league = $match->league;
        $this->home = $match->home;
        $this->away = $match->away;
        $this->ss = $match->ss;
        $this->our_event_id = $match->our_event_id;
        $this->updated_at = $match->updated_at;
    }

    /**
     * UpcomingMatch factory.
     * @param \stdClass $match
     * @return UpcomingMatch
     * @throws \Exception
     */
    public static function factory(\stdClass $match)
    {
        return new static($match);
    }

    /**
     * @param \stdClass $match
     * @throws \Exception
     */
    private function validate(\stdClass $match)
    {
        foreach (self::FIELDS as $field) {
            if (is_null($match->{$field})) {
                throw new \Exception("Field $field not found at this match");
            }
        }
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSportId(): int
    {
        return $this->sport_id;
    }

    /**
     * @return int
     */
    public function getTime(): int
    {
        return $this->time;
    }

    /**
     * @return int
     */
    public function getTimeStatus(): int
    {
        return $this->time_status;
    }

    /**
     * @return \stdClass
     */
    public function getLeague(): \stdClass
    {
        return $this->league;
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
    public function getAway(): \stdClass
    {
        return $this->away;
    }

    /**
     * @return string
     */
    public function getSs(): string
    {
        return $this->ss;
    }

    /**
     * @return int
     */
    public function getBetsAPIId(): int
    {
        return $this->our_event_id;
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->updated_at;
    }
}
