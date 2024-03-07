<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class CategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_category_api_request_unauthorized_when_not_logged_in(): void
    {
        $response = $this->getJson('/api/categories');

        $response->assertUnauthorized(); //401 status code
    }

    public function test_authorized_api_user_can_get_all_categories(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('category.jpg');

        $category = [
            'name' => 'Category 1',
            'slug' => 'Category 1',
            'image' => $file, 
            'order' => "1",
        ];

        $this->actingAs($this->user)->postJson('/api/categories', $category);

        $response = $this->actingAs($this->user)->getJson('/api/categories');

        $response->assertSuccessful(); //200 status code

        Storage::disk('public')->assertExists('images/category/'.$file->hashName());

        $response
            ->assertJson(fn (AssertableJson $json) =>
                $json->has(1)
                    ->first(fn (AssertableJson $json) =>
                        $json->where('id', 1)
                        ->where('name', 'Category 1')
                        ->where('slug', fn (string $slug) => str($slug)->is(Str::slug('Category 1')))
                        ->where('image', 'http://localhost:8000/storage/images/category/'.$file->hashName())
                        ->missing('order')
                    )
            );

    }

    public function test_create_category_api_request_unauthorized_when_not_logged_in(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('category.jpg');

        $category = [
            'name' => 'Category 1',
            'slug' => 'Category 1',
            'image' => $file, 
            'order' => "1",
        ];

        $response = $this->postJson('/api/categories', $category);

        $response->assertUnauthorized();

        Storage::disk('public')->assertMissing('images/category/'.$file->hashName());
    }

    public function test_authorized_api_user_can_create_category(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('category.jpg');

        $category = [
            'name' => 'Category 1',
            'slug' => 'Category 1',
            'image' => $file, 
            'order' => "1",
        ];

        $response = $this->actingAs($this->user)->postJson('/api/categories', $category);

        $response
            ->assertStatus(201)
            ->assertJson([
                'id' => 1,
                'name' => 'Category 1',
                'slug' => Str::slug('Category 1'),
                'image' => 'http://localhost:8000/storage/images/category/'.$file->hashName(),
            ]);

        Storage::disk('public')->assertExists('images/category/'.$file->hashName());
    }

    public function test_authorized_api_create_category_throws_error(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('category.jpg');

        $category = [
            'name' => '',
            'slug' => '',
            'image' => $file, 
            'order' => "1",
        ];

        $response = $this->actingAs($this->user)->postJson('/api/categories', $category);

        $response
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['image','order'])
            ->assertInvalid(['name','slug']);

        Storage::disk('public')->assertMissing('images/category/'.$file->hashName());
    }

    public function test_authorized_api_user_can_update_category(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('category.jpg');

        $categoryData = [
            'name' => 'Category 1',
            'slug' => 'Category 1',
            'image' => $file, 
            'order' => "1",
        ];

        $category = $this->actingAs($this->user)->postJson('/api/categories', $categoryData);

        $fileNew = UploadedFile::fake()->image('category1.jpg');

        $response = $this->putJson('/api/categories/' . $category['id'], [
            'name' => 'Category New',
            'slug' => 'Category New',
            'image' => $fileNew,
        ]);
        
        $response->assertOk();

        Storage::disk('public')->assertMissing('images/category/'.$file->hashName());
        Storage::disk('public')->assertExists('images/category/'.$fileNew->hashName());
    }

    public function test_authorized_api_user_can_delete_category()
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('category.jpg');

        $categoryData = [
            'name' => 'Category 1',
            'slug' => 'Category 1',
            'image' => $file, 
            'order' => "1",
        ];

        $category = $this->actingAs($this->user)->postJson('/api/categories', $categoryData);
        
        $response = $this->actingAs($this->user)->deleteJson('/api/categories/' . $category['id']);

        $response->assertNoContent();

        $this->assertDatabaseCount('categories', 0);
    }

}
