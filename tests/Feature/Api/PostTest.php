<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\Post;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PostTest extends TestCase
{
    use RefreshDatabase;

    public function test_when_not_logged_in_get_category_api_request_returns_unauthorized(): void
    {
        $response = $this->getJson('/api/posts');

        $response->assertUnauthorized(); //401 status code
    }

}
