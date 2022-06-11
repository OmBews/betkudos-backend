<?php

namespace Tests\Feature\Console\Commands\SB\Upcoming;

use App\Contracts\Repositories\MatchRepository;
use App\Contracts\Repositories\SportsRepository;
use App\Jobs\Matches\ProcessPreMatchOdds;
use App\Models\Events\Event;
use App\Models\Teams\Team;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class OddsCommandTest extends TestCase
{
    use RefreshDatabase;

    private $signature = 'sb:upcoming-odds';

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\SportsTableSeeder::class);
    }

    public function testWillFailIfWhenAInvalidSportIdIsProvided()
    {
        $command = $this->artisan($this->signature, ['sport' => 'Invalid sports id']);

        $command->expectsOutput('Invalid sport id, try again.')
                ->assertExitCode(1);

        $command->execute();
    }

    public function testWillFailIfTheSportDoesNotExistsOnDatabase()
    {
        $command = $this->artisan($this->signature, ['sport' => 1000]);

        $command->expectsOutput('Unable to find the provided sport')
                ->assertExitCode(1);

        $command->execute();
    }

    public function testCanSearchPreMatchesAndDispatchPreMatchOddsJob()
    {
        Bus::fake([ProcessPreMatchOdds::class]);

        $sportId = 1;

        $matchesMock = $this->mock(MatchRepository::class);
        $matches = factory(Event::class, 10)->create();
        $matches->each(function ($match) {
           $match->home()->save(factory(Team::class)->make());
           $match->away()->save(factory(Team::class)->make());
        });
        $matchesMock
            ->shouldReceive('upcomingBySport')
            ->with($sportId, ['home', 'away'])
            ->andReturn($matches);

        $command = $this->artisan($this->signature, ['sport' => 1]);

        $command->expectsOutput('10 jobs were dispatched')
                ->assertExitCode(0);

        $command->execute();

        Bus::assertDispatchedTimes(ProcessPreMatchOdds::class, 10);
    }
}
