<?php

namespace App\Http\Clients\BetsAPI\Responses\Bet365;

use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\Paginated;
use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Http\Clients\BetsAPI\Responses\BetsAPIResponse;

class UpcomingResponse extends BetsAPIResponse
{
    use Paginated;

    /**
     * @var string
     */
    private $day;

    /**
     * @var int
     */
    private $sportId;

    /**
     * @var int
     */
    private $leagueId;

    /**
     * @var array
     */
    private $matches = [];

    /**
     * UpcomingResponse constructor.
     * @param string $content
     * @param int $sportId
     * @param string $day
     * @param int|null $leagueId
     * @throws \Exception
     */
    public function __construct(string $content, int $sportId, string $day, int $leagueId = null)
    {
        parent::__construct($content);

        $this->day = $day;
        $this->sportId = $sportId;
        $this->leagueId = $leagueId;

        foreach ($this->results as $result) {
            $this->matches[] = UpcomingMatch::factory($result);
        }
    }

    /**
     * UpcomingResponse factory.
     * @param string $content
     * @param int $sportId
     * @param string $day
     * @param int|null $leagueId
     * @return static
     * @throws \Exception
     */
    public static function factory(string $content, int $sportId, string $day, int $leagueId = null)
    {
        return new static($content, $sportId, $day, $leagueId);
    }

    /**
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @return string
     */
    public function getDay(): string
    {
        return $this->day;
    }

    /**
     * @return int
     */
    public function getSportId(): int
    {
        return $this->sportId;
    }

    /**
     * @return int
     */
    public function getLeagueId(): ?int
    {
        return $this->leagueId;
    }
}
