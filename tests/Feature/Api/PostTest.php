<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Post;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use App\Policies\PostPolicy;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_when_not_logged_in_get_posts_api_request_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/posts');

        $response->assertUnauthorized(); //401 status code
    }

    public function test_index_method_returns_posts_with_pagination()
    {
        // Creating sample posts
        $posts = Post::factory()->count(10)->create();

        // Simulating a request with query parameters
        $response = $this->actingAs($this->user)->getJson('/api/posts?page=1&rows_per_page=5');

        // Asserting response status
        $response->assertStatus(200);

        // Asserting correct pagination
        $response->assertJsonCount(5, 'data'); // Checking if 5 items are returned in the data key

        // Asserting structure of the response
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'slug',
                    'category_name',
                    'description',
                    'status',
                    'image',
                ]
            ],
            'links' => [
                'first',
                'last',
                'prev',
                'next',
            ],
            'meta' => [
                'current_page',
                'from',
                'last_page',
                'path',
                'links',
                'per_page',
                'to',
                'total',
            ],
        ]);
    }

    public function test_index_method_filters_posts_by_name()
    {
        // Creating sample posts
        $posts = Post::factory()->count(5)->create();

        // Creating a post with a specific title to search for
        $postToSearch = Post::factory()->create(['title' => 'Test Post']);

        // Simulating a request with a search parameter
        $response = $this->actingAs($this->user)->get('/api/posts?search_name=Test');

        // Asserting response status
        $response->assertStatus(200);

        // Asserting that only the post with the specific title is returned
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $postToSearch->id]);
    }

    public function test_index_method_filters_posts_by_status()
    {
        // Creating sample posts
        $publishedPost = Post::factory()->create(['status' => 'published']);
        $draftPost = Post::factory()->create(['status' => 'draft']);

        // Simulating a request with a status filter
        $response = $this->actingAs($this->user)->get('/api/posts?search_status=published');

        // Asserting response status
        $response->assertStatus(200);

        // Asserting that only the published post is returned
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $publishedPost->id]);

        // Asserting that draft post is not returned
        $response->assertJsonMissing(['id' => $draftPost->id]);
    }

    public function test_create_post_api_request_unauthorized_when_not_logged_in(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('post.jpg');
        $category = Category::factory()->create();

        $postData = [
            'title' => 'Post 1',
            'slug' => 'Post 1',
            'description' => 'Post description',
            'content' => 'Post content',
            'category_id' => $category->id,
            'status' => 'draft',
            'image' => $file, 
        ];

        $response = $this->postJson('/api/posts', $postData);

        $response->assertUnauthorized();

        Storage::disk('public')->assertMissing('images/posts/'.$file->hashName());
    }

    public function test_authorized_api_user_can_create_post(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('post.jpg');
        $category = Category::factory()->create();

        $postData = [
            'title' => 'Post 1',
            'slug' => 'Post 1',
            'category_id' => $category->id,
            'description' => 'Post description',
            'content' => 'Post content',
            'image' => $file,
        ];

        $response = $this->actingAs($this->user)->postJson('/api/posts', $postData);

        $response
            ->assertStatus(201)
            ->assertJson([
                'id' => 1,
                'title' => 'Post 1',
                'slug' => Str::slug('Post 1'),
                'description' => 'Post description',
                'category_name' => $category->name,
                'status' => 'draft',
                'image' => config('app.url') . '/storage/images/posts/' . $file->hashName(),
            ]);

        Storage::disk('public')->assertExists('images/posts/'.$file->hashName());
    }

    public function test_authorized_api_create_post_throws_error(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->image('post.jpg');
        $category = Category::factory()->create();

        $postData = [
            'title' => '',
            'slug' => '',
            'content' => '',
            'description' => 'desc',
            'category_id' => $category->id,
            'image' => $file, 
        ];

        $response = $this->actingAs($this->user)->postJson('/api/posts', $postData);

        $response
            ->assertUnprocessable()
            ->assertJsonMissingValidationErrors(['image','description'])
            ->assertInvalid(['title','slug','content']);

        Storage::disk('public')->assertMissing('images/posts/'.$file->hashName());
    }

    public function test_Unauthorized_User_Cannot_Update_Post()
    {
        $category = Category::factory()->create();
        $post = Post::factory()->create();

        $response = $this->putJson(route('posts.update', ['post' => $post->id]), [
            'title' => 'Post 1',
            'slug' => 'Post 1',
            'description' => 'Post description',
            'category_id' => $category->id,
            ]);

        $response->assertUnauthorized();
    }

    public function test_Created_User_Can_Update_Post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->putJson(route('posts.update', ['post' => $post->id]), [
                'title' => 'Post 1',
                'slug' => 'Post 1',
                'content' => 'Post content',
                'description' => 'Post description',
                'category_id' => $category->id,
            ]);

        $response->assertOk();

        // Make sure the post is updated as expected
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            // your updated data here
        ]);
    }

    public function test_Not_Created_User_Cannot_Update_Post()
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($this->user)
            ->putJson(route('posts.update', ['post' => $post->id]), [
                'title' => 'Post 1',
                'slug' => 'Post 1',
                'category_id' => $category->id,
                'content' => 'Post content',
                'description' => 'Post description',
            ]);

        $response->assertStatus(403);

        // Make sure the post is updated as expected
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            // your updated data here
        ]);
    }

    public function test_unauthorized_api_user_cannot_delete_post()
    {
        $post = Post::factory()->create();
        
        $response = $this->deleteJson('/api/posts/' . $post->id);

        $response->assertUnauthorized();

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_created_user_can_delete_post()
    {
        $post = Post::factory()->create(['user_id' => $this->user->id]);
        
        $response = $this->actingAs($this->user)->deleteJson('/api/posts/' . $post->id);

        $response->assertNoContent();

        $this->assertDatabaseCount('posts', 0);
    }
    
    public function test_non_created_user_cannot_delete_post()
    {
        $user = User::factory()->create();
        $post = Post::factory()->create(['user_id' => $user->id]);
        
        $response = $this->actingAs($this->user)->deleteJson('/api/posts/' . $post->id);

        $response->assertStatus(403);

        $this->assertDatabaseCount('posts', 1);
    }

    public function test_User_Cannot_Delete_Post_Without_Authorization()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a post
        $post = Post::factory()->create();

        // Ensure user cannot delete the post without authorization
        $this->actingAs($user);
        $this->assertFalse((new PostPolicy)->delete($user, $post));

        // Call the destroy method
        $response = $this->delete('/api/posts/'.$post->id);

        // Assert response
        $response->assertForbidden();

        // Ensure post is not deleted from the database
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

}
