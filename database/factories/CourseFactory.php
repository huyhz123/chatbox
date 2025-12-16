<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Course>
 */
class CourseFactory extends Factory
{
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(4, true);
        $price = fake()->numberBetween(100000, 2000000);

        return [
            'category_id' => Category::factory()->ofType('course'),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(3),
            'details' => fake()->paragraphs(3, true),
            'image' => 'https://picsum.photos/600/400?random=' . fake()->randomNumber(5),
            'price' => $price,
            'special_price' => fake()->randomElement([null, $price * 0.8, $price * 0.85]),
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'duration' => fake()->numberBetween(4, 52) . ' weeks',
            'instructor_name' => fake()->name(),
            'access_days' => fake()->randomElement([30, 60, 90, 180, 365, null]),
            'certificate' => fake()->boolean(70),
            'order' => fake()->randomNumber(2),
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
            'enrolled_count' => fake()->randomNumber(3),
        ];
    }

    /**
     * Indicate that the course should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the course should be featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }

    /**
     * Indicate that the course is for beginners.
     */
    public function beginner(): static
    {
        return $this->state(fn (array $attributes) => [
            'level' => 'beginner',
        ]);
    }
}
