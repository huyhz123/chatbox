<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use App\Models\Order;
use App\Models\Ticket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePurchaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_available_services()
    {
        Service::factory()->count(3)->create(['is_active' => true]);

        $response = $this->getJson('/api/services');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_view_service_details()
    {
        $service = Service::factory()->create();

        $response = $this->getJson("/api/services/{$service->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $service->id]);
    }

    /** @test */
    public function user_can_purchase_service()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 100000]);

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'input_data' => ['phone' => '0123456789'],
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function service_purchase_creates_order_item()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(1, $order->items()->count());
    }

    /** @test */
    public function service_purchase_calculates_correct_total()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 100000, 'special_price' => 80000]);

        $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 2,
            'payment_gateway' => 'fake',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $expectedTotal = 80000 * 2;
        $this->assertEquals($expectedTotal, $order->total);
    }

    /** @test */
    public function service_purchase_requires_authentication()
    {
        $service = Service::factory()->create();

        $response = $this->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function service_purchase_requires_valid_service()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => 99999,
            'quantity' => 1,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function service_purchase_requires_quantity()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function service_purchase_respects_min_quantity()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['min_quantity' => 5]);

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 2,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function service_purchase_respects_max_quantity()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['max_quantity' => 10]);

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 20,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function service_purchase_increments_sold_count()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['sold_count' => 0]);

        $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
        ]);

        $this->assertEquals(1, $service->refresh()->sold_count);
    }

    /** @test */
    public function service_purchase_creates_ticket_after_payment()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);

        $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
            'input_data' => ['phone' => '0123456789'],
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertGreaterThan(0, $order->tickets()->count());
    }

    /** @test */
    public function service_purchase_stores_input_data()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $inputData = ['phone' => '0123456789', 'email' => 'test@example.com'];

        $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
            'input_data' => $inputData,
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $ticket = $order->tickets()->first();
        $this->assertEquals($inputData, $ticket->input_data);
    }

    /** @test */
    public function user_can_view_purchase_history()
    {
        $user = User::factory()->create();
        Service::factory()->count(2)->each(function($service) use ($user) {
            $this->actingAs($user)->postJson('/api/services/purchase', [
                'service_id' => $service->id,
                'quantity' => 1,
                'payment_gateway' => 'fake',
            ]);
        });

        $response = $this->actingAs($user)->getJson('/api/services/purchases');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    /** @test */
    public function user_can_view_ticket_details()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $ticket->id]);
    }

    /** @test */
    public function user_can_view_their_tickets()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        Ticket::factory()->count(3)->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/tickets');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function service_purchase_returns_order_details()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['price' => 100000]);

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(201);
        $response->assertJsonStructure([
            'id',
            'order_number',
            'total',
            'payment_status',
        ]);
    }

    /** @test */
    public function inactive_service_cannot_be_purchased()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function service_purchase_creates_order_with_pending_payment()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
            'payment_gateway' => 'fake',
        ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function user_cannot_purchase_service_without_payment_gateway()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/services/purchase', [
            'service_id' => $service->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }
}
