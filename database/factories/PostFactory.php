<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Post;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Post::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence,
            'slug' => Str::slug(fake()->sentence),
            'description' => fake()->paragraph,
            'content' => fake()->text,
            //'image' => fake()->imageUrl(),
            'image' => 'images/posts/vH9xwLAulbnOG1QmaSXHd69pYnC3LpCHcPfu69NJ.jpg',
            'status' => 'draft', // You can customize this as needed
            'user_id' => function () {
                return \App\Models\User::factory()->create()->id;
            },
            'category_id' => function () {
                return \App\Models\Category::factory()->create()->id;
            },
            'created_at' => fake()->dateTimeBetween('-1 year', 'now'),
            'updated_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
