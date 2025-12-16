<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\File;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    protected $model = File::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->numberBetween(5000, 500000);
        $fileTypes = ['pdf', 'zip', 'rar', 'exe', 'docx', 'xlsx', 'pptx'];
        $fileType = fake()->randomElement($fileTypes);

        return [
            'category_id' => Category::factory()->ofType('file'),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'details' => fake()->paragraphs(2, true),
            'image' => 'https://picsum.photos/400/400?random=' . fake()->randomNumber(5),
            'file_path' => '/storage/files/' . Str::random(32) . '.' . $fileType,
            'file_type' => $fileType,
            'file_size' => fake()->numberBetween(1000000, 500000000), // 1MB to 500MB
            'version' => fake()->numerify('v#.#.#'),
            'price' => $price,
            'special_price' => fake()->randomElement([null, $price * 0.8, $price * 0.85]),
            'download_limit' => fake()->randomElement([null, 1, 5, 10, 50]),
            'is_encrypted' => fake()->boolean(20),
            'preview_url' => 'https://example.com/preview/' . Str::random(20),
            'order' => fake()->randomNumber(2),
            'is_active' => true,
            'is_featured' => fake()->boolean(20),
            'sold_count' => fake()->randomNumber(3),
            'download_count' => fake()->randomNumber(3),
        ];
    }

    /**
     * Indicate that the file should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the file should be featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
