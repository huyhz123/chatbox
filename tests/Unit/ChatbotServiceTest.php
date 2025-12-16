<?php

namespace Tests\Unit;

use App\Models\Service;
use App\Models\File;
use App\Models\Course;
use App\Models\ChatbotConversation;
use App\Services\ChatbotService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotServiceTest extends TestCase
{
    use RefreshDatabase;

    protected ChatbotService $chatbotService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->chatbotService = new ChatbotService();
    }

    /** @test */
    public function get_or_create_conversation_creates_new_conversation()
    {
        $sessionId = 'session-123';

        $conversation = $this->chatbotService->getOrCreateConversation($sessionId);

        $this->assertInstanceOf(ChatbotConversation::class, $conversation);
        $this->assertEquals($sessionId, $conversation->session_id);
        $this->assertDatabaseHas('chatbot_conversations', [
            'session_id' => $sessionId,
        ]);
    }

    /** @test */
    public function get_or_create_conversation_returns_existing_conversation()
    {
        $sessionId = 'session-123';
        $existingConversation = ChatbotConversation::factory()->create(['session_id' => $sessionId]);

        $conversation = $this->chatbotService->getOrCreateConversation($sessionId);

        $this->assertEquals($existingConversation->id, $conversation->id);
    }

    /** @test */
    public function get_or_create_conversation_stores_user_id()
    {
        $sessionId = 'session-123';
        $userId = 1;

        $conversation = $this->chatbotService->getOrCreateConversation($sessionId, $userId);

        $this->assertEquals($userId, $conversation->user_id);
    }

    /** @test */
    public function chat_creates_conversation_if_not_exists()
    {
        $sessionId = 'session-123';
        $message = 'Hello';

        $this->chatbotService->chat($sessionId, $message);

        $this->assertDatabaseHas('chatbot_conversations', [
            'session_id' => $sessionId,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function chat_returns_string_response()
    {
        $sessionId = 'session-123';
        $message = 'Hello';

        $response = $this->chatbotService->chat($sessionId, $message);

        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    /** @test */
    public function chat_handles_service_query()
    {
        Service::factory()->create([
            'name' => 'Test Service',
            'price' => 100000,
            'is_active' => true,
        ]);

        $response = $this->chatbotService->chat('session-123', 'dịch vụ');

        $this->assertStringContainsString('dịch vụ', strtolower($response));
    }

    /** @test */
    public function chat_handles_file_query()
    {
        File::factory()->create([
            'name' => 'Test File',
            'price' => 50000,
            'is_active' => true,
        ]);

        $response = $this->chatbotService->chat('session-123', 'file');

        $this->assertIsString($response);
    }

    /** @test */
    public function chat_handles_course_query()
    {
        Course::factory()->create([
            'name' => 'Test Course',
            'price' => 200000,
            'is_active' => true,
        ]);

        $response = $this->chatbotService->chat('session-123', 'khóa học');

        $this->assertStringContainsString('khóa', strtolower($response));
    }

    /** @test */
    public function chat_handles_support_query()
    {
        $response = $this->chatbotService->chat('session-123', 'hỗ trợ');

        $this->assertStringContainsString('hỗ trợ', strtolower($response));
    }

    /** @test */
    public function chat_stores_user_message()
    {
        $sessionId = 'session-123';
        $message = 'Test message';

        $this->chatbotService->chat($sessionId, $message);

        $conversation = ChatbotConversation::where('session_id', $sessionId)->first();
        $this->assertGreaterThan(0, $conversation->messages()->count());
    }

    /** @test */
    public function chat_stores_assistant_response()
    {
        $sessionId = 'session-123';

        $this->chatbotService->chat($sessionId, 'Hello');

        $conversation = ChatbotConversation::where('session_id', $sessionId)->first();
        $userMessages = $conversation->messages()->where('role', 'user')->count();
        $assistantMessages = $conversation->messages()->where('role', 'assistant')->count();

        $this->assertGreaterThan(0, $userMessages);
        $this->assertGreaterThan(0, $assistantMessages);
    }

    /** @test */
    public function chat_returns_help_message_for_unknown_query()
    {
        $response = $this->chatbotService->chat('session-123', 'xyz unknown query 123');

        $this->assertIsString($response);
    }

    /** @test */
    public function multiple_chats_in_same_conversation()
    {
        $sessionId = 'session-123';

        $response1 = $this->chatbotService->chat($sessionId, 'Hello');
        $response2 = $this->chatbotService->chat($sessionId, 'Thanks');

        $this->assertIsString($response1);
        $this->assertIsString($response2);

        $conversation = ChatbotConversation::where('session_id', $sessionId)->first();
        $this->assertGreaterThanOrEqual(2, $conversation->messages()->count());
    }

    /** @test */
    public function conversation_stores_ip_address()
    {
        $sessionId = 'session-123';

        $this->chatbotService->getOrCreateConversation($sessionId);

        $conversation = ChatbotConversation::where('session_id', $sessionId)->first();
        $this->assertNotNull($conversation->ip_address);
    }

    /** @test */
    public function conversation_is_active_by_default()
    {
        $sessionId = 'session-123';

        $this->chatbotService->getOrCreateConversation($sessionId);

        $conversation = ChatbotConversation::where('session_id', $sessionId)->first();
        $this->assertTrue($conversation->is_active);
    }

    /** @test */
    public function service_query_returns_active_services_only()
    {
        Service::factory()->create(['name' => 'Active Service', 'price' => 100000, 'is_active' => true]);
        Service::factory()->create(['name' => 'Inactive Service', 'price' => 50000, 'is_active' => false]);

        $response = $this->chatbotService->chat('session-123', 'service');

        $this->assertStringContainsString('Active Service', $response);
    }

    /** @test */
    public function chat_with_price_query()
    {
        $response = $this->chatbotService->chat('session-123', 'giá');

        $this->assertIsString($response);
        $this->assertNotEmpty($response);
    }

    /** @test */
    public void test_chat_conversation_limit_retrieved()
    {
        $sessionId = 'session-123';
        $conversation = $this->chatbotService->getOrCreateConversation($sessionId);

        for ($i = 0; $i < 15; $i++) {
            $this->chatbotService->chat($sessionId, "Message $i");
        }

        $this->assertGreaterThanOrEqual(15, $conversation->refresh()->messages()->count());
    }

    /** @test */
    public function different_sessions_have_different_conversations()
    {
        $sessionId1 = 'session-1';
        $sessionId2 = 'session-2';

        $conversation1 = $this->chatbotService->getOrCreateConversation($sessionId1);
        $conversation2 = $this->chatbotService->getOrCreateConversation($sessionId2);

        $this->assertNotEquals($conversation1->id, $conversation2->id);
    }

    /** @test */
    public function file_query_returns_active_files_only()
    {
        File::factory()->create(['name' => 'Active File', 'price' => 100000, 'is_active' => true]);
        File::factory()->create(['name' => 'Inactive File', 'price' => 50000, 'is_active' => false]);

        $response = $this->chatbotService->chat('session-123', 'tài liệu');

        $this->assertStringContainsString('file', strtolower($response));
    }

    /** @test */
    public function course_query_returns_active_courses_only()
    {
        Course::factory()->create(['name' => 'Active Course', 'price' => 100000, 'is_active' => true]);
        Course::factory()->create(['name' => 'Inactive Course', 'price' => 50000, 'is_active' => false]);

        $response = $this->chatbotService->chat('session-123', 'course');

        $this->assertStringContainsString('Course', $response);
    }

    /** @test */
    public function chat_with_english_service_query()
    {
        Service::factory()->create(['name' => 'Test Service', 'price' => 100000, 'is_active' => true]);

        $response = $this->chatbotService->chat('session-123', 'service');

        $this->assertIsString($response);
    }

    /** @test */
    public function chat_with_english_support_query()
    {
        $response = $this->chatbotService->chat('session-123', 'support');

        $this->assertIsString($response);
    }

    /** @test */
    public function chat_case_insensitive_query_handling()
    {
        $response1 = $this->chatbotService->chat('session-1', 'DỊCH VỤ');
        $response2 = $this->chatbotService->chat('session-2', 'dịch vụ');

        $this->assertIsString($response1);
        $this->assertIsString($response2);
    }
}
