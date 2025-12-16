<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\File;
use App\Models\Order;
use App\Models\FileDownload;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilePurchaseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_available_files()
    {
        File::factory()->count(3)->create(['is_active' => true]);

        $response = $this->getJson('/api/files');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_view_file_details()
    {
        $file = File::factory()->create();

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $file->id]);
    }

    /** @test */
    public function file_preview_is_accessible_if_available()
    {
        $file = File::factory()->create([
            'preview_url' => 'https://example.com/preview.pdf',
        ]);

        $response = $this->getJson("/api/files/{$file->id}/preview");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_purchase_file()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['price' => 50000]);

        $response = $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function file_purchase_creates_order_item()
    {
        $user = User::factory()->create();
        $file = File::factory()->create();

        $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
            'payment_gateway' => 'fake',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(1, $order->items()->count());
    }

    /** @test */
    public function file_purchase_calculates_correct_total()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['price' => 100000, 'special_price' => 80000]);

        $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
            'payment_gateway' => 'fake',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(80000, $order->total);
    }

    /** @test */
    public function file_purchase_requires_authentication()
    {
        $file = File::factory()->create();

        $response = $this->postJson('/api/files/purchase', [
            'file_id' => $file->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function file_purchase_requires_valid_file()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => 99999,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function file_purchase_requires_payment_gateway()
    {
        $user = User::factory()->create();
        $file = File::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function file_purchase_increments_sold_count()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['sold_count' => 0]);

        $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
            'payment_gateway' => 'fake',
        ]);

        $this->assertEquals(1, $file->refresh()->sold_count);
    }

    /** @test */
    public function user_can_download_purchased_file()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['file_path' => 'uploads/test.pdf']);
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/files/{$file->id}/download");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_download_unpurchased_file()
    {
        $user = User::factory()->create();
        $file = File::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/files/{$file->id}/download");

        $response->assertStatus(403);
    }

    /** @test */
    public function download_increments_download_count()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['file_path' => 'uploads/test.pdf', 'download_count' => 0]);
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'order_id' => $order->id,
        ]);

        $this->actingAs($user)->getJson("/api/files/{$file->id}/download");

        $this->assertEquals(1, $file->refresh()->download_count);
    }

    /** @test */
    public function user_can_download_file_multiple_times_if_allowed()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['file_path' => 'uploads/test.pdf', 'download_limit' => 0]);
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'order_id' => $order->id,
        ]);

        $response1 = $this->actingAs($user)->getJson("/api/files/{$file->id}/download");
        $response2 = $this->actingAs($user)->getJson("/api/files/{$file->id}/download");

        $response1->assertStatus(200);
        $response2->assertStatus(200);
    }

    /** @test */
    public function user_cannot_exceed_download_limit()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['file_path' => 'uploads/test.pdf', 'download_limit' => 1]);
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        $download = FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'order_id' => $order->id,
            'download_count' => 1,
        ]);

        $response = $this->actingAs($user)->getJson("/api/files/{$file->id}/download");

        $response->assertStatus(403);
    }

    /** @test */
    public function inactive_file_cannot_be_purchased()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function user_can_view_purchased_files()
    {
        $user = User::factory()->create();
        $file1 = File::factory()->create();
        $file2 = File::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file1->id,
            'order_id' => $order->id,
        ]);

        FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file2->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($user)->getJson('/api/files/purchased');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    /** @test */
    public function user_can_view_file_download_history()
    {
        $user = User::factory()->create();
        $file = File::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        FileDownload::factory()->count(3)->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/files/{$file->id}/downloads");

        $response->assertStatus(200);
    }

    /** @test */
    public function file_purchase_returns_order_details()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['price' => 50000]);

        $response = $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
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
    public function file_download_logs_download_activity()
    {
        $user = User::factory()->create();
        $file = File::factory()->create(['file_path' => 'uploads/test.pdf']);
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'order_id' => $order->id,
        ]);

        $this->actingAs($user)->getJson("/api/files/{$file->id}/download");

        $this->assertDatabaseHas('file_downloads', [
            'user_id' => $user->id,
            'file_id' => $file->id,
        ]);
    }

    /** @test */
    public function file_with_special_price_uses_special_price()
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'price' => 100000,
            'special_price' => 70000,
        ]);

        $this->actingAs($user)->postJson('/api/files/purchase', [
            'file_id' => $file->id,
            'payment_gateway' => 'fake',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(70000, $order->total);
    }

    /** @test */
    public function encrypted_files_are_downloadable()
    {
        $user = User::factory()->create();
        $file = File::factory()->create([
            'file_path' => 'uploads/encrypted.bin',
            'is_encrypted' => true,
        ]);
        $order = Order::factory()->create(['user_id' => $user->id, 'payment_status' => 'paid']);

        FileDownload::factory()->create([
            'user_id' => $user->id,
            'file_id' => $file->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/files/{$file->id}/download");

        $response->assertStatus(200);
    }

    /** @test */
    public function file_version_is_accessible()
    {
        $file = File::factory()->create([
            'version' => '2.0',
        ]);

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(200);
        $response->assertJson(['version' => '2.0']);
    }

    /** @test */
    public function file_size_information_is_available()
    {
        $file = File::factory()->create([
            'file_size' => 2097152,
        ]);

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure(['id', 'file_size']);
    }
}
