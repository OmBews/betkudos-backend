<?php

namespace Tests\Feature\Http\Controllers\API;

use App\Models\Countries\Country;
use App\Models\Events\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use function Amp\Parallel\Worker\create;

class CountryControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\CountryTableSeeder::class);
        $this->seed(\SportsTableSeeder::class);
    }

    protected function withMatchesRoute(int $sportId)
    {
        return route('countries.index', ['sport' => $sportId]);
    }

    public function testUserCanGetCountriesWithMatchesList()
    {
        $sport = 1;

        $countries = Country::all();
        $countries->each(function (Country $country) use ($sport) {
            factory(Event::class, 1)->create([
                'time_status' => Event::STATUS_NOT_STARTED,
                'starts_at' => rand(strtotime('+10 min'), strtotime(Event::UPCOMING_DAYS_LIMIT)),
                'sport_id' => $sport,
                'cc' => $country->code
            ]);
        });

        $response = $this->getJson($this->withMatchesRoute($sport));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'
        ]);
        $response->assertJsonCount($countries->count(), 'data');
    }
}
