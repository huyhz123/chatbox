<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketServiceTest extends TestCase
{
    use RefreshDatabase;

    protected TicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ticketService = new TicketService();
    }

    /** @test */
    public function create_ticket_creates_ticket_record()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket = $this->ticketService->createTicket($order, $service);

        $this->assertInstanceOf(Ticket::class, $ticket);
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id,
            'user_id' => $user->id,
            'service_id' => $service->id,
        ]);
    }

    /** @test */
    public function create_ticket_generates_ticket_number()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket = $this->ticketService->createTicket($order, $service);

        $this->assertNotNull($ticket->ticket_number);
        $this->assertStringStartsWith('TK', $ticket->ticket_number);
    }

    /** @test */
    public function create_ticket_sets_pending_status()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket = $this->ticketService->createTicket($order, $service);

        $this->assertEquals('pending', $ticket->status);
    }

    /** @test */
    public function create_ticket_stores_input_data()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $inputData = ['phone' => '0123456789', 'email' => 'test@example.com'];

        $ticket = $this->ticketService->createTicket($order, $service, $inputData);

        $this->assertEquals($inputData, $ticket->input_data);
    }

    /** @test */
    public function create_ticket_records_submitted_at()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket = $this->ticketService->createTicket($order, $service);

        $this->assertNotNull($ticket->submitted_at);
    }

    /** @test */
    public function create_ticket_stores_service_api_provider()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'dhru']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket = $this->ticketService->createTicket($order, $service);

        $this->assertEquals('dhru', $ticket->api_provider);
    }

    /** @test */
    public function create_ticket_stores_order_reference()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket = $this->ticketService->createTicket($order, $service);

        $this->assertEquals($order->id, $ticket->order_id);
    }

    /** @test */
    public function process_ticket_sets_processing_status()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'order_id' => $order->id,
            'status' => 'pending',
            'api_provider' => 'manual',
        ]);

        $this->ticketService->processTicket($ticket);

        $this->assertEquals('processing', $ticket->refresh()->status);
    }

    /** @test */
    public function update_ticket_from_api_requires_api_order_id()
    {
        $ticket = Ticket::factory()->create(['api_order_id' => null]);

        $result = $this->ticketService->updateTicketFromApi($ticket);

        $this->assertFalse($result);
    }

    /** @test */
    public function ticket_numbers_are_unique()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order1 = Order::factory()->create(['user_id' => $user->id]);
        $order2 = Order::factory()->create(['user_id' => $user->id]);

        $ticket1 = $this->ticketService->createTicket($order1, $service);
        $ticket2 = $this->ticketService->createTicket($order2, $service);

        $this->assertNotEquals($ticket1->ticket_number, $ticket2->ticket_number);
    }

    /** @test */
    public function multiple_tickets_for_same_order()
    {
        $user = User::factory()->create();
        $service1 = Service::factory()->create(['api_provider' => 'manual']);
        $service2 = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket1 = $this->ticketService->createTicket($order, $service1);
        $ticket2 = $this->ticketService->createTicket($order, $service2);

        $this->assertEquals(2, $order->tickets()->count());
    }

    /** @test */
    public function create_ticket_creates_history_record()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);

        $ticket = $this->ticketService->createTicket($order, $service);

        $this->assertGreaterThan(0, $ticket->history()->count());
    }

    /** @test */
    public function ticket_status_transitions_are_tracked()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $ticket->updateStatus('processing', 'Started processing');

        $this->assertEquals('processing', $ticket->refresh()->status);
        $this->assertGreaterThan(0, $ticket->history()->count());
    }

    /** @test */
    public function ticket_service_stores_ticket_with_all_data()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create([
            'api_provider' => 'dhru',
            'api_service_id' => 'SVC-123',
        ]);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $inputData = ['name' => 'Test'];

        $ticket = $this->ticketService->createTicket($order, $service, $inputData);

        $this->assertEquals($user->id, $ticket->user_id);
        $this->assertEquals($service->id, $ticket->service_id);
        $this->assertEquals($order->id, $ticket->order_id);
        $this->assertEquals($inputData, $ticket->input_data);
    }

    /** @test */
    public function ticket_processed_at_timestamp_recorded()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'order_id' => $order->id,
            'status' => 'pending',
            'processed_at' => null,
        ]);

        $ticket->updateStatus('processing');

        $this->assertNotNull($ticket->refresh()->processed_at);
    }

    /** @test */
    public function ticket_completed_at_timestamp_recorded()
    {
        $user = User::factory()->create();
        $service = Service::factory()->create(['api_provider' => 'manual']);
        $order = Order::factory()->create(['user_id' => $user->id]);
        $ticket = Ticket::factory()->create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'order_id' => $order->id,
            'status' => 'processing',
            'completed_at' => null,
        ]);

        $ticket->updateStatus('completed', 'Work completed');

        $this->assertNotNull($ticket->refresh()->completed_at);
    }
}
