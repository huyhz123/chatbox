<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Voucher>
 */
class VoucherFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed']);

        return [
            'code' => strtoupper($this->faker->lexify('??????')),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'discount_value' => $type === 'percentage'
                ? $this->faker->numberBetween(5, 50)
                : $this->faker->numberBetween(10, 100),
            'min_order_amount' => $this->faker->numberBetween(20, 100),
            'max_discount_amount' => $type === 'percentage'
                ? $this->faker->numberBetween(50, 200)
                : null,
            'usage_limit' => $this->faker->numberBetween(10, 100),
            'usage_limit_per_user' => $this->faker->numberBetween(1, 3),
            'used_count' => 0,
            'starts_at' => now(),
            'expires_at' => now()->addMonths($this->faker->numberBetween(1, 6)),
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the voucher is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the voucher is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the voucher is a percentage discount.
     */
    public function percentage(int $value = 10): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'percentage',
            'discount_value' => $value,
            'max_discount_amount' => $value * 5,
        ]);
    }

    /**
     * Indicate that the voucher is a fixed discount.
     */
    public function fixed(int $value = 20): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'fixed',
            'discount_value' => $value,
            'max_discount_amount' => null,
        ]);
    }
}
