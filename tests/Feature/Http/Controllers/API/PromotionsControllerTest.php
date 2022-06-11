<?php

namespace Tests\Feature\API;

use App\Models\Promotions\Promotion;
use App\Models\Users\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Passport\Passport;
use Tests\TestCase;

class PromotionsControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\RolesAndPermissionsSeeder::class);
    }

    /**
     * @return string
     */
    protected function promotionsIndexRoute()
    {
        return route('promotions.index');
    }

    /**
     * @return string
     */
    protected function storePromotionsRoute()
    {
        return route('promotions.store');
    }

    protected function updatePromotionRoute(int $promotion)
    {
        return route('promotions.update', ['promotion' => $promotion]);
    }

    protected function getBase64File(string $filename = 'promotion.png'): string
    {
        $file = UploadedFile::fake()->image(
            $filename,
            Promotion::IMAGE_WIDTH,
            Promotion::IMAGE_HEIGHT
        );

        return "data:{$file->getMimeType()};base64," . base64_encode($file->get());
    }

    // Create promotions

    public function testUserCanRetrieveAllPromotions()
    {
        factory(Promotion::class, 3)->create();

        $response = $this->getJson($this->promotionsIndexRoute());

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    public function testUserCanCreatePromotions()
    {
        Storage::fake('promotions');

        $promotions = factory(Promotion::class, 3)->make([
            'image' => $this->getBase64File()
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->storePromotionsRoute(), [
            'promotions' => $promotions
        ]);

        $response->assertCreated();
        $response->assertJsonCount(3, 'data');
        foreach (Promotion::all() as $promotion) {
            Storage::disk('promotions')->assertExists($promotion->image);
        }
    }

    public function testUserCanNotCreatePromotionsWithoutPermission()
    {
        Storage::fake('promotions');

        $promotions = factory(Promotion::class, 3)->make([
            'image' => $this->getBase64File()
        ]);

        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->postJson($this->storePromotionsRoute(), [
            'promotions' => $promotions
        ]);

        $response->assertForbidden();
    }

    public function testUserCanNotCreateMoreThanThreePromotions()
    {
        $promotions = factory(Promotion::class, 4)->make([
            'image' => $this->getBase64File()
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->storePromotionsRoute(), [
            'promotions' => $promotions
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'promotions' => 'The promotions may not have more than 3 items.'
        ]);
    }

    public function testUserCanNotAddMorePromotionsIfMaxLimitIsReached()
    {
        factory(Promotion::class, 3)->create();
        $promotions = factory(Promotion::class, 3)->make([
            'image' => $this->getBase64File()
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->storePromotionsRoute(), [
            'promotions' => $promotions
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'promotions' => trans('bost.promotions.max', ['max' => count($promotions)])
        ]);
    }

    public function testUserCanNotCreatePromotionsWithoutValidBase64Images()
    {
        $promotions = factory(Promotion::class, 1)->make([
            'image' => 'Invalid image content'
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->storePromotionsRoute(), [
            'promotions' => $promotions
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'promotions.0.image' => trans('validation.base64_image', [
                'dimensions' => Promotion::IMAGE_WIDTH . "x" . Promotion::IMAGE_HEIGHT
            ])
        ]);
    }

    public function testUserCanNotCreateAPromotionWithANameThatAlreadyExists()
    {
        $alreadyCreatedPromotion = factory(Promotion::class)->create();

        $promotions = factory(Promotion::class, 1)->make([
            'name' => $alreadyCreatedPromotion->name,
            'image' => $this->getBase64File()
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->postJson($this->storePromotionsRoute(), [
            'promotions' => $promotions
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'promotions.0.name' => 'The promotions.0.name has already been taken.'
        ]);
    }

    // Update promotions

    public function testUserCanUpdateAPromotion()
    {
        Storage::fake('promotions');

        $promotion = factory(Promotion::class)->create([
            'image' => 'old-image-name.jpeg'
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->putJson($this->updatePromotionRoute($promotion->id), [
            'name' => $name = 'New Promotion Name',
            'image' => $this->getBase64File('promotion.png'),
            'priority' => $priority = 1,
        ]);

        $promotion->refresh();

        $response->assertSuccessful();
        $response->assertJsonStructure([
            'data'
        ]);

        $this->assertEquals(Str::slug($name) . '.jpeg', $promotion->image);
        $this->assertEquals($priority, $promotion->priority);

        Storage::disk('promotions')->assertExists($promotion->image);
    }

    public function testUserCanNotUpdateAPromotionWithoutPermission()
    {
        $promotion = factory(Promotion::class)->create([
            'image' => 'old-image-name.jpeg'
        ]);
        $user = factory(User::class)->create();

        Passport::actingAs($user);

        $response = $this->putJson($this->updatePromotionRoute($promotion->id));

        $response->assertForbidden();
    }

    public function testUserCanNotUpdateAPromotionWithAInvalidPromotionName()
    {
        $promotion = factory(Promotion::class)->create([
            'image' => 'old-image-name.jpeg'
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->putJson($this->updatePromotionRoute($promotion->id), [
            'name' => '',
            'image' => $this->getBase64File('promotion.png'),
            'priority' => $priority = 1,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'name' => 'The name field is required.'
        ]);
    }

    public function testUserCanNotUpdateAPromotionWithAInvalidPromotionImage()
    {
        $promotion = factory(Promotion::class)->create([
            'image' => 'old-image-name.jpeg'
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->putJson($this->updatePromotionRoute($promotion->id), [
            'name' => 'Some Promotion Name',
            'image' => '',
            'priority' => 1,
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'image' => 'The image field is required.'
        ]);
    }

    public function testUserCanNotUpdateAPromotionWithAInvalidPromotionPriority()
    {
        $promotion = factory(Promotion::class)->create([
            'image' => 'old-image-name.jpeg'
        ]);

        $user = factory(User::class)->create();
        $user->assignRole('bookie');

        Passport::actingAs($user);

        $response = $this->putJson($this->updatePromotionRoute($promotion->id), [
            'name' => 'Some Promotion Name',
            'image' => $this->getBase64File('promotion.png'),
            'priority' => '',
        ]);

        $response->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
        $response->assertJsonValidationErrors([
            'priority' => 'The priority field is required.'
        ]);
    }
}
