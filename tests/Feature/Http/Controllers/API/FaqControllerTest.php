<?php

namespace Tests\Feature\Http\Controllers\API;

use App\Contracts\Repositories\FaqRepository;
use App\Models\FAQs\Faq;
use App\Models\Users\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Laravel\Passport\Passport;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FaqControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);
    }

    protected function faqsIndexRoute()
    {
        return route('faqs.index');
    }

    protected function faqsDeleteRoute(Faq $faq)
    {
        return route('faqs.destroy', ['faq' => $faq->id]);
    }

    protected function faqsStoreRoute()
    {
        return route('faqs.store');
    }

    protected function faqsUpdateRoute(Faq $faq)
    {
        return route('faqs.update', ['faq' => $faq->id]);
    }

    public function faqWelcomeRoute()
    {
        return route('api.faqs-welcome');
    }

    protected function invalidQuestion()
    {
        return [
            'question' => null,
            'answer' => $this->faker->text,
            'welcome' => $this->faker->boolean,
            'priority' => $this->faker->randomNumber(),
        ];
    }

    protected function invalidAnswer()
    {
        return [
            'question' => $this->faker->text,
            'answer' => null,
            'welcome' => $this->faker->boolean,
            'priority' => $this->faker->randomNumber(),
        ];
    }

    protected function invalidWelcome()
    {
        return [
            'question' => $this->faker->text,
            'answer' => $this->faker->text,
            'welcome' => 'some data',
            'priority' => $this->faker->randomNumber(),
        ];
    }

    protected function invalidPriority()
    {
        return [
            'question' => $this->faker->text,
            'answer' => $this->faker->text,
            'welcome' => $this->faker->boolean,
            'priority' => 'some data type',
        ];
    }

    protected function buildFaqs(int $total = 1)
    {
        $faqs = [];

        for ($i = 0; $i < $total; $i++) {
            $faqs[] = [
                'question' => $this->faker->text,
                'answer' => $this->faker->text,
                'welcome' => $this->faker->boolean,
                'priority' => $this->faker->randomNumber(),
            ];
        }

        return $faqs;
    }

    public function testUserCanCreateFaqs()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        $faqs = $this->buildFaqs(5);

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => $faqs
        ]);

        $response->assertCreated();
        $response->assertJsonStructure(['faqs']);
        $response->assertJsonCount(5, 'faqs');
    }


    public function testCanNotCreateAFaqWithAQuestionThatAlreadyExists()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id,
        ]);

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => [
                [
                    'question' => $faq->question,
                    'answer' => $this->faker->text,
                    'welcome' => $this->faker->boolean,
                    'priority' => $this->faker->randomNumber(),
                ]
            ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'faqs.0.question' => 'The faqs.0.question has already been taken.'
        ]);
    }

    public function testUserCanNotCreateFaqWithoutPermission()
    {
        $user = factory(User::class)->create();

        $faqs = $this->buildFaqs(5);

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => $faqs
        ]);

        $response->assertForbidden();
    }

    public function testShouldRequiredArrayToCreateFaqs()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => 'Some data type'
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function testShouldReturnCorrectMessageWhenFaqCreationFails()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        $faqs = $this->buildFaqs();

        Passport::actingAs($user);

        $this->mock(FaqRepository::class, function ($mock) use ($user, $faqs) {
            $mock->shouldReceive('createMany')
                 ->with($faqs, $user)
                 ->andThrow(new \Exception());
        });

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => $faqs
        ]);

        $response->assertStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
        $response->assertJson([
            'message' => trans('bost.faqs.failed', ['action' => 'save'])
        ]);
    }

    public function testShouldNotCreateAFaqWithoutAQuestion()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => [ $this->invalidQuestion() ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
           'faqs.0.question' => 'The faqs.0.question field is required.'
        ]);
    }

    public function testShouldNotCreateAFaqWithoutAAnswer()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => [ $this->invalidAnswer() ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'faqs.0.answer' => 'The faqs.0.answer field is required.'
        ]);
    }

    public function testShouldNotCreateAFaqWithInvalidWelcome()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => [ $this->invalidWelcome() ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'faqs.0.welcome' => 'The faqs.0.welcome field must be true or false.'
        ]);
    }

    public function testShouldNotCreateAFaqWithInvalidPriority()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->faqsStoreRoute(), [
            'faqs' => [ $this->invalidPriority() ]
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'faqs.0.priority' => 'The faqs.0.priority must be an integer.'
        ]);
    }


    public function testUserCanRetrieveAllWelcomeFaqs()
    {
        factory(Faq::class, 10)->create([
            'user_id' => 1,
            'last_editor_id' => 1,
            'welcome' => true
        ]);
        $response = $this->getJson($this->faqWelcomeRoute());

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data'
        ]);
        $response->assertJsonCount(10, 'data');
    }

    public function testCanReturnAListOfFAQs()
    {
        $user = factory(User::class)->create();
        $faqs = factory(Faq::class, 10)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->getJson($this->faqsIndexRoute());

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data'
        ]);
        $response->assertJsonCount(10, 'data');
    }

    public function testUserCanUpdateAFaq()
    {
        $user = factory(User::class)->create();
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->putJson($this->faqsUpdateRoute($faq), [
            'question' => $question = 'What is the question?',
            'answer' => $answer = 'This is a answer!',
            'welcome' => $welcome = ! $faq->welcome,
            'priority' => $priority = rand(1, 10),
        ]);

        $faq->refresh();

        $response->assertSuccessful();

        $this->assertEquals($question, $faq->question);
        $this->assertEquals($answer, $faq->answer);
        $this->assertEquals($welcome, $faq->welcome);
        $this->assertEquals($priority, $faq->priority);
        $this->assertEquals($user->id, $faq->last_editor_id);
    }

    public function testShouldUpdateTheLastEditorId()
    {
        $firstEditor = factory(User::class)->create();
        $currentEditor = factory(User::class)->create();
        $currentEditor->assignRole('bookie');
        $faq = factory(Faq::class)->create([
            'user_id' => $firstEditor->id,
            'last_editor_id' => $firstEditor->id
        ]);

        Passport::actingAs($currentEditor);


        $response = $this->putJson($this->faqsUpdateRoute($faq), [
            'question' => $question = 'What is the question?',
            'answer' => $answer = 'This is a answer!',
            'welcome' => $welcome = ! $faq->welcome,
            'priority' => $priority = rand(1, 10),
        ]);

        $faq->refresh();

        $response->assertSuccessful();

        $this->assertEquals($currentEditor->id, $faq->last_editor_id);
    }

    public function testUserCanNotUpdateAFaqWithAQuestionThatAlreadyExists()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');
        $someFaq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->putJson($this->faqsUpdateRoute($faq), [
            'question' => $someFaq->question,
            'answer' => $faq->question,
            'welcome' => $faq->welcome,
            'priority' => $faq->priority,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'question' => 'The question has already been taken.'
        ]);
    }

    public function testUserCaNotUpdateFaqWithoutPermission()
    {
        $user = factory(User::class)->create();
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->putJson($this->faqsUpdateRoute($faq), [
            'question' => $question = 'What is the question?',
            'answer' => $answer = 'This is a answer!',
            'welcome' => $welcome = ! $faq->welcome,
            'priority' => $priority = rand(1, 99999),
        ]);

        $faq->refresh();

        $response->assertForbidden();

        $this->assertNotEquals($question, $faq->question);
        $this->assertNotEquals($answer, $faq->answer);
        $this->assertNotEquals($welcome, $faq->welcome);
        $this->assertNotEquals($priority, $faq->priority);
    }

    public function testUserCaNotUpdateFaqWithoutQuestion()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->putJson(
            $this->faqsUpdateRoute($faq),
            $this->invalidQuestion()
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
           'question' => 'The question field is required.'
        ]);
    }

    public function testUserCaNotUpdateFaqWithoutAAnswer()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->putJson(
            $this->faqsUpdateRoute($faq),
            $this->invalidAnswer()
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'answer' => 'The answer field is required.'
        ]);
    }

    public function testUserCaNotUpdateFaqWithAInvalidWelcome()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->putJson(
            $this->faqsUpdateRoute($faq),
            $this->invalidWelcome()
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'welcome' => 'The welcome field must be true or false.'
        ]);
    }

    public function testUserCaNotUpdateFaqWithAInvalidPriority()
    {
        $user = factory(User::class)->create();
        $user->assignRole('bookie');
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->putJson(
            $this->faqsUpdateRoute($faq),
            $this->invalidPriority()
        );

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'priority' => 'The priority must be an integer.'
        ]);
    }

    public function testUserCanDestroyAFaq()
    {
        $user = factory(User::class)->create();
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);
        $user->assignRole('bookie');
        $user->givePermissionTo('delete faqs');

        Passport::actingAs($user);

        $response = $this->deleteJson($this->faqsDeleteRoute($faq));

        $response->assertSuccessful();
        $response->assertJson([
            'deleted' => true
        ]);

        $this->assertTrue(Faq::find($faq->id) === null);
    }

    public function testUserCanNotDestroyFaqsWithoutDeletePermission()
    {
        $user = factory(User::class)->create();
        $faq = factory(Faq::class)->create([
            'user_id' => $user->id,
            'last_editor_id' => $user->id
        ]);

        Passport::actingAs($user);

        $response = $this->deleteJson($this->faqsDeleteRoute($faq));

        $response->assertForbidden();

        $this->assertFalse(Faq::find($faq->id) === null);
    }
}
