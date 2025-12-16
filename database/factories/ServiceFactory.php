<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true);
        $price = fake()->numberBetween(10000, 500000);
        $cost = fake()->numberBetween(5000, $price - 1000);

        return [
            'category_id' => Category::factory()->ofType('service'),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'details' => fake()->paragraphs(2, true),
            'image' => 'https://picsum.photos/400/300?random=' . fake()->randomNumber(5),
            'images' => [
                'https://picsum.photos/400/300?random=' . fake()->randomNumber(5),
                'https://picsum.photos/400/300?random=' . fake()->randomNumber(5),
            ],
            'price' => $price,
            'cost' => $cost,
            'special_price' => fake()->randomElement([null, $price * 0.8, $price * 0.85, $price * 0.9]),
            'api_provider' => fake()->randomElement(['local', 'smm_panel', 'api_vendor', null]),
            'api_service_id' => fake()->randomElement([null, fake()->uuid(), fake()->randomNumber(5)]),
            'min_quantity' => fake()->numberBetween(1, 10),
            'max_quantity' => fake()->numberBetween(100, 1000),
            'processing_time' => fake()->numberBetween(1, 72) . ' hours',
            'required_fields' => [
                ['name' => 'target', 'label' => 'Target URL/Username', 'type' => 'text'],
                ['name' => 'quantity', 'label' => 'Quantity', 'type' => 'number'],
            ],
            'order' => fake()->randomNumber(2),
            'is_active' => true,
            'is_featured' => fake()->boolean(30),
            'sold_count' => fake()->randomNumber(4),
        ];
    }

    /**
     * Indicate that the service should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the service should be featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
