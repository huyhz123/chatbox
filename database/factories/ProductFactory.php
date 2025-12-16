<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(4, true);
        $price = fake()->numberBetween(50000, 5000000);
        $cost = fake()->numberBetween(20000, $price - 10000);
        $stock = fake()->numberBetween(0, 500);

        return [
            'category_id' => Category::factory()->ofType('product'),
            'name' => ucfirst($name),
            'slug' => Str::slug($name),
            'sku' => strtoupper(Str::random(8)),
            'description' => fake()->paragraph(),
            'details' => fake()->paragraphs(2, true),
            'image' => 'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
            'images' => [
                'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
                'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
                'https://picsum.photos/500/500?random=' . fake()->randomNumber(5),
            ],
            'price' => $price,
            'cost' => $cost,
            'special_price' => fake()->randomElement([null, $price * 0.75, $price * 0.80, $price * 0.85]),
            'stock' => $stock,
            'low_stock_alert' => 20,
            'stock_status' => $stock > 0 ? 'in_stock' : 'out_of_stock',
            'order' => fake()->randomNumber(2),
            'is_active' => true,
            'is_featured' => fake()->boolean(25),
            'sold_count' => fake()->randomNumber(4),
            'weight' => fake()->randomFloat(2, 0.5, 50),
            'attributes' => [
                'color' => fake()->randomElement(['Red', 'Blue', 'Green', 'Black', 'White']),
                'size' => fake()->randomElement(['S', 'M', 'L', 'XL', 'XXL']),
                'material' => fake()->randomElement(['Cotton', 'Polyester', 'Wool', 'Silk']),
            ],
        ];
    }

    /**
     * Indicate that the product should be inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the product should be out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
            'stock_status' => 'out_of_stock',
        ]);
    }

    /**
     * Indicate that the product should be featured.
     */
    public function featured(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }
}
