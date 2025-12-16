<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_initiate_payment()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);
        $order = Order::factory()->create(['user_id' => $user->id, 'total' => 100000]);

        $response = $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function payment_requires_valid_order()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => 99999,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function payment_requires_user_to_own_order()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function payment_supports_multiple_gateways()
    {
        $gateways = ['fake', 'stripe', 'paypal'];

        foreach ($gateways as $gateway) {
            $user = User::factory()->create();
            $order = Order::factory()->create(['user_id' => $user->id, 'total' => 100000]);

            $response = $this->actingAs($user)->postJson('/api/payments/initiate', [
                'order_id' => $order->id,
                'payment_gateway' => $gateway,
            ]);

            $response->assertStatus(201);
        }
    }

    /** @test */
    public function user_can_verify_payment()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->getJson("/api/payments/{$payment->id}/verify");

        $response->assertStatus(200);
    }

    /** @test */
    public function payment_callback_updates_order_status()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson('/api/payments/callback/fake', [
            'payment_id' => $payment->id,
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function successful_payment_marks_order_as_paid()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $this->postJson('/api/payments/callback/fake', [
            'payment_id' => $payment->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
        ]);
    }

    /** @test */
    public function user_can_view_payment_history()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        Payment::factory()->count(3)->create(['order_id' => $order->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/payments');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_view_payment_details()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $payment->id]);
    }

    /** @test */
    public function user_cannot_view_other_users_payments()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user1->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id, 'user_id' => $user1->id]);

        $response = $this->actingAs($user2)->getJson("/api/payments/{$payment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function payment_tracks_transaction_id()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(201);
        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertNotNull($payment->id);
    }

    /** @test */
    public function payment_stores_request_data()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total' => 100000]);
        $requestData = ['custom_field' => 'value'];

        $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
            'data' => $requestData,
        ]);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertIsArray($payment->request_data);
    }

    /** @test */
    public function failed_payment_keeps_order_pending()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $this->postJson('/api/payments/callback/fake', [
            'payment_id' => $payment->id,
            'status' => 'failed',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function user_can_retry_payment()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        $response = $this->actingAs($user)->postJson('/api/payments/retry', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(201);
        $payments = Payment::where('order_id', $order->id)->get();
        $this->assertGreaterThanOrEqual(2, $payments->count());
    }

    /** @test */
    public function payment_currency_is_recorded()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'currency' => 'VND',
        ]);

        $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('VND', $payment->currency);
    }

    /** @test */
    public function payment_amount_matches_order_total()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 500000,
        ]);

        $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals(500000, $payment->amount);
    }

    /** @test */
    public function multiple_payments_for_same_order_allowed()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response1 = $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $response2 = $this->actingAs($user)->postJson('/api/payments/initiate', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);

        $response1->assertStatus(201);
        $response2->assertStatus(201);
    }

    /** @test */
    public function payment_refund_workflow()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->postJson("/api/payments/{$payment->id}/refund", []);

        $response->assertStatus(200);
    }
}
