<?php

namespace Tests\Feature\Console\Commands\SB\Upcoming;

use App\Console\Commands\SB\Upcoming\MatchesCommand;
use App\Contracts\Repositories\MatchRepository;
use App\Contracts\Services\BetsAPI\Bet365ServiceInterface;
use App\Http\Clients\BetsAPI\Responses\Bet365\Entities\UpcomingMatch;
use App\Http\Clients\BetsAPI\Responses\Bet365\UpcomingResponse;
use App\Jobs\Matches\ProcessResults;
use App\Jobs\Matches\ProcessUpcoming;
use App\Models\Events\Event;
use App\Models\Sports\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class MatchesCommandTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $signature = 'sb:upcoming-matches';

    protected function findNewMatches(Sport $sport)
    {
        return $this->artisan($this->signature, [
            'sport' => $sport->id,
            '--find-new' => true
        ]);
    }

    protected function updateNextMatches(Sport $sport)
    {
        return $this->artisan($this->signature, [
            'sport' => $sport->id,
            '--update' => MatchesCommand::UPDATE_NEXT_MATCHES
        ]);
    }

    protected function updatePreMatches(Sport $sport)
    {
        return $this->artisan($this->signature, [
            'sport' => $sport->id,
            '--update' => MatchesCommand::UPDATE_PRE_MATCHES
        ]);
    }

    public function testCanValidateSportsIdArgument()
    {
        $this->artisan($this->signature, ['sport' => 'Some invalid id'])
             ->expectsOutput('A valid sport ID is required')
             ->assertExitCode(1)
             ->execute();
    }

    public function testCanCheckIfSportIsActive()
    {
        $sport = factory(Sport::class)->create([
            'active' => false
        ]);

        $this->artisan($this->signature, ['sport' => $sport->id])
             ->expectsOutput("The {$sport->name} sport is currently blocked")
             ->assertExitCode(1)
             ->execute();
    }

    public function testCanCheckIfSportNotExists()
    {
        $this->artisan($this->signature, ['sport' => $this->faker->randomDigit])
             ->expectsOutput("Sport not found")
             ->assertExitCode(1)
             ->execute();
    }

    public function testCanValidateUpdateOption()
    {
        $sport = factory(Sport::class)->create([
            'active' => true
        ]);

        $this->artisan($this->signature, ['sport' => $sport->id, '--update' => 'Invalid option'])
             ->expectsOutput("The update option provided is invalid: Invalid option")
             ->assertExitCode(1)
             ->execute();
    }

    public function testCanFindNewEventsAndDispatchJobs()
    {
        Bus::fake([ProcessUpcoming::class]);

        $sport = factory(Sport::class)->create([
            'active' => true,
            'time_frame' => $timeFrame = 2
        ]);

        $upcomingMatchMock = \Mockery::mock(UpcomingMatch::class);
        $upcomingResponseMock = \Mockery::mock(UpcomingResponse::class);

        $upcomingResponseMock
            ->shouldReceive('getMatches')
            ->andReturn([
                 $upcomingMatchMock,
                 $upcomingMatchMock,
                 $upcomingMatchMock
             ]);

        $upcomingResponseMock
            ->shouldReceive('hasMorePages')
            ->andReturn(false);

        $this->mock(Bet365ServiceInterface::class)
            ->shouldReceive('makeUpcomingRequest')
            ->times(3)
            ->andReturn(promise(function () use($upcomingResponseMock) {
                return $upcomingResponseMock;
            }));

        $command = $this->findNewMatches($sport);

        $command->expectsOutput("Searching for new {$sport->name} upcoming matches")
                ->expectsOutput("in the next $timeFrame days")
                ->expectsOutput("All jobs was dispatched")
                ->assertExitCode(0);

        $command->execute();

        Bus::assertDispatchedTimes(ProcessUpcoming::class, 9);
    }

    public function testCanDispatchJobsToUpdateNextMatches()
    {
        Bus::fake([ProcessResults::class]);

        $sport = factory(Sport::class)->create([
            'active' => true
        ]);

        $matchRepositoryMock = $this->mock(MatchRepository::class);

        $matchRepositoryMock
            ->shouldReceive('nextMatches')
            ->once()
            ->with($sport->id)
            ->andReturn(factory(Event::class, 10)->make());

        $command = $this->updateNextMatches($sport);

        $command->expectsOutput('updating next matches')
                ->expectsOutput('Dispatching next matches jobs...')
                ->expectsOutput('All next matches jobs was dispatched')
                ->assertExitCode(0);

        $command->execute();

        Bus::assertDispatchedTimes(ProcessResults::class, 10);
    }

    public function testCanDispatchJobsToUpdatePreMatches()
    {
        Bus::fake([ProcessResults::class]);

        $sport = factory(Sport::class)->create([
            'active' => true
        ]);

        $matchRepositoryMock = $this->mock(MatchRepository::class);

        $matchRepositoryMock
            ->shouldReceive('preMatches')
            ->once()
            ->with($sport->id)
            ->andReturn(factory(Event::class, 10)->make());

        $command = $this->updatePreMatches($sport);

        $command->expectsOutput('updating pre matches')
                ->expectsOutput('Dispatching pre matches jobs...')
                ->expectsOutput('All pre matches jobs was dispatched')
                ->assertExitCode(0);

        $command->execute();

        Bus::assertDispatchedTimes(ProcessResults::class, 10);
    }
}
