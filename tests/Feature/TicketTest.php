<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Ticket;
use App\Models\Service;
use App\Models\Order;
use App\Models\TicketHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_their_tickets()
    {
        $user = User::factory()->create();
        Ticket::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/tickets');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
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
    public function user_cannot_view_other_users_ticket()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user1->id]);

        $response = $this->actingAs($user2)->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_create_ticket()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/tickets', [
            'service_id' => $service->id,
            'input_data' => [
                'phone' => '0123456789',
                'email' => 'test@example.com',
            ],
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);
    }

    /** @test */
    public function ticket_creation_generates_ticket_number()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/tickets', [
            'service_id' => $service->id,
            'input_data' => ['phone' => '0123456789'],
        ]);

        $response->assertStatus(201);
        $ticket = Ticket::where('user_id', $user->id)->first();
        $this->assertStringStartsWith('TK', $ticket->ticket_number);
    }

    /** @test */
    public function ticket_creation_requires_service()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/tickets', [
            'service_id' => 99999,
            'input_data' => ['phone' => '0123456789'],
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function ticket_starts_with_pending_status()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user)->postJson('/api/tickets', [
            'service_id' => $service->id,
            'input_data' => ['phone' => '0123456789'],
        ]);

        $ticket = Ticket::where('user_id', $user->id)->first();
        $this->assertEquals('pending', $ticket->status);
    }

    /** @test */
    public function user_can_add_comment_to_ticket()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson("/api/tickets/{$ticket->id}/comments", [
            'message' => 'I need more information',
        ]);

        $response->assertStatus(201);
    }

    /** @test */
    public function user_can_view_ticket_history()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);
        TicketHistory::factory()->count(3)->create(['ticket_id' => $ticket->id]);

        $response = $this->actingAs($user)->getJson("/api/tickets/{$ticket->id}/history");

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function ticket_status_can_be_updated()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);

        $ticket->updateStatus('processing', 'Started processing');

        $this->assertEquals('processing', $ticket->refresh()->status);
    }

    /** @test */
    public function ticket_status_change_creates_history()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id]);

        $ticket->updateStatus('processing', 'Manual processing');

        $this->assertGreaterThan(0, $ticket->history()->count());
    }

    /** @test */
    public function user_can_filter_tickets_by_status()
    {
        $user = User::factory()->create();
        Ticket::factory()->create(['user_id' => $user->id, 'status' => 'pending']);
        Ticket::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Ticket::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user)->getJson('/api/tickets?status=pending');

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
    }

    /** @test */
    public function ticket_shows_service_information()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'name' => 'Test Service',
            'price' => 100000,
        ]);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'service' => ['id', 'name'],
        ]);
    }

    /** @test */
    public function ticket_records_submitted_time()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create();

        $this->actingAs($user)->postJson('/api/tickets', [
            'service_id' => $service->id,
            'input_data' => ['phone' => '0123456789'],
        ]);

        $ticket = Ticket::where('user_id', $user->id)->first();
        $this->assertNotNull($ticket->submitted_at);
    }

    /** @test */
    public function admin_can_view_all_tickets()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        Ticket::factory()->count(5)->create();

        $response = $this->actingAs($admin)->getJson('/api/admin/tickets');

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function admin_can_update_ticket_status()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $ticket = Ticket::factory()->create(['status' => 'pending']);

        $response = $this->actingAs($admin)->patchJson("/api/admin/tickets/{$ticket->id}", [
            'status' => 'processing',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'status' => 'processing',
        ]);
    }

    /** @test */
    public function admin_can_add_ticket_notes()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $ticket = Ticket::factory()->create(['notes' => null]);

        $response = $this->actingAs($admin)->patchJson("/api/admin/tickets/{$ticket->id}", [
            'notes' => 'Customer needs additional support',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'notes' => 'Customer needs additional support',
        ]);
    }

    /** @test */
    public function admin_can_mark_ticket_completed()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $ticket = Ticket::factory()->create(['status' => 'processing', 'completed_at' => null]);

        $response = $this->actingAs($admin)->patchJson("/api/admin/tickets/{$ticket->id}", [
            'status' => 'completed',
            'result' => 'Work completed successfully',
        ]);

        $response->assertStatus(200);
        $this->assertNotNull($ticket->refresh()->completed_at);
    }

    /** @test */
    public function ticket_can_track_api_response()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'dhru']);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'api_response' => ['status' => 'pending'],
        ]);

        $response = $this->actingAs($user)->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200);
    }

    /** @test */
    public function ticket_list_is_paginated()
    {
        $user = User::factory()->create();
        Ticket::factory()->count(25)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/tickets?per_page=10');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
    }

    /** @test */
    public function user_can_search_tickets_by_number()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create(['user_id' => $user->id, 'ticket_number' => 'TK202412121234AB']);

        $response = $this->actingAs($user)->getJson('/api/tickets?search=TK202412121234AB');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
    }

    /** @test */
    public function ticket_can_be_reopened()
    {
        $user = User::factory()->create();
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
        ]);

        $response = $this->actingAs($user)->postJson("/api/tickets/{$ticket->id}/reopen", [
            'reason' => 'Issue not resolved',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('pending', $ticket->refresh()->status);
    }

    /** @test */
    public function unauthenticated_user_cannot_view_tickets()
    {
        $response = $this->getJson('/api/tickets');

        $response->assertStatus(401);
    }

    /** @test */
    public function ticket_shows_related_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'order_id' => $order->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'order' => ['id'],
        ]);
    }

    /** @test */
    public function ticket_input_data_is_stored_correctly()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['required_fields' => ['phone', 'email']]);
        $inputData = ['phone' => '0123456789', 'email' => 'test@example.com'];

        $response = $this->actingAs($user)->postJson('/api/tickets', [
            'service_id' => $service->id,
            'input_data' => $inputData,
        ]);

        $ticket = Ticket::where('user_id', $user->id)->first();
        $this->assertEquals($inputData, $ticket->input_data);
    }
}
