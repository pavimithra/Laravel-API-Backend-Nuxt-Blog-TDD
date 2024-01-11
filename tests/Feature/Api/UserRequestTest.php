<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson;
use App\Models\User;

class UserRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_user_api_request_unauthorized_when_not_logged_in(): void
    {
        $response = $this->getJson('/api/user');

        $response->assertUnauthorized(); //401 status code
    }

    public function test_authorized_api_user_can_get_logged_in_user_details(): void
    {
        $user = User::factory()->create(['name' => 'Admin', 'email' => 'admin@gmail.com']);

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertSuccessful(); //200 status code

        $response->assertJsonStructure(['id', 'name', 'email']);

        $response
        ->assertJson(fn (AssertableJson $json) =>
            $json->where('id', 2)
                 ->where('name', 'Admin')
                 ->where('email', fn (string $email) => str($email)->is('admin@gmail.com'))
        );

    }
}
