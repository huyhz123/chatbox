<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->numberBetween(50000, 5000000);
        $discount = fake()->randomElement([0, $subtotal * 0.1, $subtotal * 0.15, $subtotal * 0.2]);
        $tax = fake()->numberBetween(0, ($subtotal - $discount) * 0.1);
        $total = $subtotal - $discount + $tax;

        return [
            'order_number' => 'ORD-' . fake()->numerify('##########'),
            'user_id' => User::factory(),
            'type' => fake()->randomElement(['service', 'product', 'file', 'course']),
            'status' => fake()->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'payment_status' => fake()->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => fake()->randomElement(['credit_card', 'bank_transfer', 'wallet', 'paypal']),
            'payment_gateway' => fake()->randomElement(['stripe', 'paypal', 'bank', 'vnpay']),
            'transaction_id' => fake()->uuid(),
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'total' => $total,
            'currency' => 'VND',
            'customer_name' => fake()->name(),
            'customer_email' => fake()->email(),
            'customer_phone' => fake()->phoneNumber(),
            'customer_address' => fake()->address(),
            'notes' => fake()->optional(0.3)->sentence(),
            'ip_address' => fake()->ipv4(),
            'paid_at' => fake()->randomElement([null, fake()->dateTimeBetween('-60 days')]),
        ];
    }

    /**
     * Indicate that the order should be paid.
     */
    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'paid',
            'status' => 'completed',
            'paid_at' => now(),
        ]);
    }

    /**
     * Indicate that the order should be pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);
    }
}
