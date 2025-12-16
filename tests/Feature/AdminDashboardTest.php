<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use App\Models\Product;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function non_admin_cannot_access_admin_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/admin/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_admin_dashboard()
    {
        $response = $this->getJson('/api/admin/dashboard');

        $response->assertStatus(401);
    }

    /** @test */
    public function admin_can_view_dashboard_statistics()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Order::factory()->count(5)->create(['payment_status' => 'paid']);
        Order::factory()->count(3)->create(['payment_status' => 'pending']);

        $response = $this->actingAs($admin)->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_orders',
            'total_revenue',
            'pending_orders',
        ]);
    }

    /** @test */
    public function admin_can_view_all_orders()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Order::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/orders');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function admin_can_view_order_details()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = Order::factory()->create();

        $response = $this->actingAs($admin)->getJson("/api/admin/orders/{$order->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $order->id]);
    }

    /** @test */
    public function admin_can_update_order_status()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $order = Order::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->patchJson("/api/admin/orders/{$order->id}", [
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function admin_can_view_all_products()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Product::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/products');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function admin_can_create_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->postJson('/api/admin/products', [
            'name' => 'New Product',
            'price' => 100000,
            'cost' => 40000,
            'stock' => 50,
            'sku' => 'SKU-001',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'name' => 'New Product',
        ]);
    }

    /** @test */
    public function admin_can_update_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->patchJson("/api/admin/products/{$product->id}", [
            'name' => 'Updated Name',
            'price' => 150000,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'price' => 150000,
        ]);
    }

    /** @test */
    public function admin_can_delete_product()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $product = Product::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    /** @test */
    public function admin_can_view_all_services()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Service::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/services');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function admin_can_create_service()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->postJson('/api/admin/services', [
            'name' => 'New Service',
            'price' => 50000,
            'cost' => 20000,
            'api_provider' => 'manual',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('services', [
            'name' => 'New Service',
        ]);
    }

    /** @test */
    public function admin_can_update_service()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $service = Service::factory()->create();

        $response = $this->actingAs($admin)->patchJson("/api/admin/services/{$service->id}", [
            'name' => 'Updated Service',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Updated Service',
        ]);
    }

    /** @test */
    public function admin_can_delete_service()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $service = Service::factory()->create();

        $response = $this->actingAs($admin)->deleteJson("/api/admin/services/{$service->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_user_list()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6, 'data');
    }

    /** @test */
    public function admin_can_view_user_details()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->getJson("/api/admin/users/{$user->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $user->id]);
    }

    /** @test */
    public function admin_can_deactivate_user()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $user = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($admin)->patchJson("/api/admin/users/{$user->id}", [
            'is_active' => false,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_active' => false,
        ]);
    }

    /** @test */
    public function admin_can_view_revenue_report()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Order::factory()->count(5)->create(['payment_status' => 'paid', 'total' => 100000]);

        $response = $this->actingAs($admin)->getJson('/api/admin/reports/revenue');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_sales_report()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Order::factory()->count(5)->create(['payment_status' => 'paid']);

        $response = $this->actingAs($admin)->getJson('/api/admin/reports/sales');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_export_orders()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Order::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/orders/export');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_system_logs()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->getJson('/api/admin/logs');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_manage_settings()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $response = $this->actingAs($admin)->patchJson('/api/admin/settings', [
            'site_name' => 'My Shop',
            'support_email' => 'support@example.com',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_view_dashboard_with_filters()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Order::factory()->count(10)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/dashboard?period=month');

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_can_bulk_update_orders()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $orders = Order::factory()->count(3)->create();
        $orderIds = $orders->pluck('id')->toArray();

        $response = $this->actingAs($admin)->patchJson('/api/admin/orders/bulk', [
            'order_ids' => $orderIds,
            'status' => 'completed',
        ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function non_admin_cannot_create_product()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/admin/products', [
            'name' => 'New Product',
            'price' => 100000,
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_admin_cannot_update_product()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)->patchJson("/api/admin/products/{$product->id}", [
            'name' => 'Updated Name',
        ]);

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_dashboard_shows_key_metrics()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Order::factory()->count(5)->create(['payment_status' => 'paid', 'total' => 100000]);

        $response = $this->actingAs($admin)->getJson('/api/admin/dashboard');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'total_orders',
            'total_revenue',
            'total_products',
            'total_users',
        ]);
    }
}
