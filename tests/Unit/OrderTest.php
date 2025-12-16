<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function order_can_be_created()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-001',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_number' => 'ORD-001',
        ]);
    }

    /** @test */
    public function order_has_required_attributes()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'total' => 1000000,
            'currency' => 'VND',
        ]);

        $this->assertEquals('pending', $order->status);
        $this->assertEquals('pending', $order->payment_status);
        $this->assertEquals(1000000, $order->total);
        $this->assertEquals('VND', $order->currency);
    }

    /** @test */
    public function status_scope_filters_by_status()
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        Order::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $pendingOrders = Order::status('pending')->get();

        $this->assertEquals(2, $pendingOrders->count());
    }

    /** @test */
    public function paid_scope_filters_paid_orders()
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);
        Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);
        Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'pending']);

        $paidOrders = Order::paid()->get();

        $this->assertEquals(2, $paidOrders->count());
    }

    /** @test */
    public function pending_scope_filters_pending_orders()
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'pending']);
        Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        $pendingOrders = Order::pending()->get();

        $this->assertEquals(1, $pendingOrders->count());
    }

    /** @test */
    public function is_paid_returns_true_for_paid_orders()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'paid',
        ]);

        $this->assertTrue($order->isPaid());
    }

    /** @test */
    public function is_paid_returns_false_for_unpaid_orders()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        $this->assertFalse($order->isPaid());
    }

    /** @test */
    public function mark_as_paid_updates_payment_status()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        $order->markAsPaid('TRX-12345');

        $refreshed = $order->refresh();
        $this->assertEquals('paid', $refreshed->payment_status);
        $this->assertEquals('TRX-12345', $refreshed->transaction_id);
        $this->assertNotNull($refreshed->paid_at);
    }

    /** @test */
    public function mark_as_paid_without_transaction_id()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        $order->markAsPaid();

        $refreshed = $order->refresh();
        $this->assertEquals('paid', $refreshed->payment_status);
    }

    /** @test */
    public function calculate_total_with_no_items()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
        ]);

        $order->calculateTotal();

        $this->assertEquals(0, $order->refresh()->total);
    }

    /** @test */
    public function calculate_total_with_items()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'discount' => 50000,
            'tax' => 0,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'price' => 100000,
            'quantity' => 2,
        ]);

        $order->calculateTotal();

        $expected = (100000 * 2) - 50000 + 0;
        $this->assertEquals($expected, $order->refresh()->total);
    }

    /** @test */
    public function calculate_total_with_discount_and_tax()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'discount' => 100000,
            'tax' => 50000,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'price' => 500000,
            'quantity' => 1,
        ]);

        $order->calculateTotal();

        $expected = 500000 - 100000 + 50000;
        $this->assertEquals($expected, $order->refresh()->total);
    }

    /** @test */
    public function get_total_profit_with_no_items()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $profit = $order->getTotalProfit();

        $this->assertEquals(0, $profit);
    }

    /** @test */
    public function get_total_profit_calculates_correctly()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'price' => 100000,
            'cost' => 40000,
            'quantity' => 2,
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'price' => 500000,
            'cost' => 300000,
            'quantity' => 1,
        ]);

        $profit = $order->getTotalProfit();
        $expected = (100000 - 40000) * 2 + (500000 - 300000) * 1;

        $this->assertEquals($expected, $profit);
    }

    /** @test */
    public function order_belongs_to_user()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $order->user);
        $this->assertEquals($user->id, $order->user->id);
    }

    /** @test */
    public function order_has_many_items()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        OrderItem::factory()->count(3)->create(['order_id' => $order->id]);

        $this->assertEquals(3, $order->items()->count());
    }

    /** @test */
    public function order_has_many_payments()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertEquals(0, $order->payments()->count());
    }

    /** @test */
    public function order_has_many_tickets()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->assertEquals(0, $order->tickets()->count());
    }

    /** @test */
    public function order_soft_delete_works()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $orderId = $order->id;

        $order->delete();

        $this->assertNull(Order::find($orderId));
        $this->assertNotNull(Order::withTrashed()->find($orderId));
    }

    /** @test */
    public function order_total_is_cast_to_decimal()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => '1999.99',
        ]);

        $this->assertIsNumeric($order->total);
    }

    /** @test */
    public function order_paid_at_is_cast_to_datetime()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $order->markAsPaid();

        $this->assertInstanceOf(\DateTime::class, $order->refresh()->paid_at);
    }

    /** @test */
    public function multiple_orders_for_single_user()
    {
        $user = User::factory()->create();
        Order::factory()->count(5)->create(['user_id' => $user->id]);

        $this->assertEquals(5, $user->orders()->count());
    }

    /** @test */
    public function order_stores_customer_information()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_phone' => '0123456789',
            'customer_address' => '123 Main St',
        ]);

        $this->assertEquals('John Doe', $order->customer_name);
        $this->assertEquals('john@example.com', $order->customer_email);
    }

    /** @test */
    public function order_status_scope_and_paid_scope_can_be_chained()
    {
        $user = User::factory()->create();
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'payment_status' => 'paid',
        ]);
        Order::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'payment_status' => 'pending',
        ]);

        $orders = Order::status('completed')->paid()->get();

        $this->assertEquals(1, $orders->count());
    }
}
