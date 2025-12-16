<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    protected $model = Ticket::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['pending', 'processing', 'completed', 'failed']);
        $submittedAt = fake()->dateTimeBetween('-30 days');

        return [
            'ticket_number' => 'TKT-' . fake()->numerify('##########'),
            'user_id' => User::factory(),
            'service_id' => Service::factory(),
            'order_id' => Order::factory(),
            'status' => $status,
            'api_provider' => fake()->randomElement(['local', 'smm_panel', 'api_vendor']),
            'api_order_id' => fake()->optional(0.5)->uuid(),
            'input_data' => [
                'target' => 'https://example.com/user',
                'quantity' => fake()->numberBetween(10, 1000),
            ],
            'api_request' => [
                'method' => 'POST',
                'endpoint' => '/api/order',
                'timestamp' => now()->toIso8601String(),
            ],
            'api_response' => fake()->optional(0.8)->passthrough([
                'status' => 'success',
                'order_id' => fake()->uuid(),
                'message' => 'Order processed successfully',
            ]),
            'result' => fake()->optional(0.6)->passthrough([
                'completed' => fake()->numberBetween(50, 1000),
                'status' => $status === 'completed' ? 'completed' : 'processing',
            ]),
            'notes' => fake()->optional(0.4)->sentence(),
            'submitted_at' => $submittedAt,
            'processed_at' => $status !== 'pending' ? fake()->dateTimeBetween($submittedAt, now()) : null,
            'completed_at' => $status === 'completed' ? fake()->dateTimeBetween($submittedAt, now()) : null,
        ];
    }

    /**
     * Indicate that the ticket should be completed.
     */
    public function completed(): static
    {
        $submittedAt = fake()->dateTimeBetween('-30 days');
        $processedAt = fake()->dateTimeBetween($submittedAt, now());
        $completedAt = fake()->dateTimeBetween($processedAt, now());

        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'submitted_at' => $submittedAt,
            'processed_at' => $processedAt,
            'completed_at' => $completedAt,
        ]);
    }

    /**
     * Indicate that the ticket should be pending.
     */
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'processed_at' => null,
            'completed_at' => null,
        ]);
    }
}
