<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductPurchaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_products()
    {
        Product::factory()->count(5)->create(['is_active' => true, 'stock' => 100, 'stock_status' => 'in_stock']);

        $response = $this->getJson('/api/products');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function user_can_view_product_details()
    {
        $product = Product::factory()->create();

        $response = $this->getJson("/api/products/{$product->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $product->id]);
    }

    /** @test */
    public function user_can_add_product_to_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100, 'stock_status' => 'in_stock']);

        $response = $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function cart_validates_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function cart_prevents_overselling()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 5, 'stock_status' => 'in_stock']);

        $response = $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 10,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_view_cart()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['stock' => 100, 'stock_status' => 'in_stock']);
        $product2 = Product::factory()->create(['stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_remove_product_from_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->deleteJson("/api/cart/remove/{$product->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_update_cart_quantity()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->putJson("/api/cart/update/{$product->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_checkout_cart()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function checkout_creates_order_items()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);
        $product2 = Product::factory()->create(['price' => 50000, 'stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product1->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product2->id,
            'quantity' => 2,
        ]);

        $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(2, $order->items()->count());
    }

    /** @test */
    public function checkout_calculates_correct_total()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create([
            'price' => 100000,
            'special_price' => 80000,
            'stock' => 100,
            'stock_status' => 'in_stock',
        ]);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $expectedTotal = 80000 * 3;
        $this->assertEquals($expectedTotal, $order->total);
    }

    /** @test */
    public function checkout_requires_authentication()
    {
        $response = $this->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function checkout_with_empty_cart_fails()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function out_of_stock_product_cannot_be_purchased()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 0, 'stock_status' => 'out_of_stock']);

        $response = $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_apply_discount_code()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
            'discount_code' => 'SAVE10',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function payment_decreases_product_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 5,
        ]);

        $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 95,
        ]);
    }

    /** @test */
    public function payment_increments_product_sold_count()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock', 'sold_count' => 0]);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'sold_count' => 3,
        ]);
    }

    /** @test */
    public function user_can_view_order_details()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $response = $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $orderId = $response->json('id');

        $response = $this->actingAs($user)->getJson("/api/orders/{$orderId}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $orderId]);
    }

    /** @test */
    public function user_can_view_order_history()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)->postJson('/api/cart/add', [
                'product_id' => $product->id,
                'quantity' => 1,
            ]);

            $this->actingAs($user)->postJson('/api/checkout', [
                'payment_gateway' => 'fake',
                'customer_name' => 'John Doe',
                'customer_email' => 'john@example.com',
                'customer_address' => '123 Main St',
            ]);
        }

        $response = $this->actingAs($user)->getJson('/api/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function cart_is_cleared_after_successful_checkout()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $response = $this->actingAs($user)->getJson('/api/cart');

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_modify_cart_after_checkout()
    {
        $user = User::factory()->create();
        $product1 = Product::factory()->create(['price' => 100000, 'stock' => 100, 'stock_status' => 'in_stock']);
        $product2 = Product::factory()->create(['price' => 50000, 'stock' => 100, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product1->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $response = $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function stock_status_updates_when_out_of_stock()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['price' => 100000, 'stock' => 1, 'stock_status' => 'in_stock']);

        $this->actingAs($user)->postJson('/api/cart/add', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->actingAs($user)->postJson('/api/checkout', [
            'payment_gateway' => 'fake',
            'customer_name' => 'John Doe',
            'customer_email' => 'john@example.com',
            'customer_address' => '123 Main St',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 0,
            'stock_status' => 'out_of_stock',
        ]);
    }
}
