<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseProgress;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseEnrollmentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_available_courses()
    {
        Course::factory()->count(3)->create(['is_active' => true]);

        $response = $this->getJson('/api/courses');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function user_can_view_course_details()
    {
        $course = Course::factory()->create();

        $response = $this->getJson("/api/courses/{$course->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $course->id]);
    }

    /** @test */
    public function user_can_view_course_lessons()
    {
        $course = Course::factory()->create();
        CourseLesson::factory()->count(5)->create(['course_id' => $course->id]);

        $response = $this->getJson("/api/courses/{$course->id}/lessons");

        $response->assertStatus(200);
        $response->assertJsonCount(5, 'data');
    }

    /** @test */
    public function user_can_enroll_in_course()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 299000]);

        $response = $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
    }

    /** @test */
    public function course_enrollment_creates_enrollment_record()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $this->assertDatabaseHas('course_enrollments', [
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);
    }

    /** @test */
    public function course_enrollment_calculates_correct_total()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 500000, 'special_price' => 399000]);

        $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $order = Order::where('user_id', $user->id)->first();
        $this->assertEquals(399000, $order->total);
    }

    /** @test */
    public function course_enrollment_requires_authentication()
    {
        $course = Course::factory()->create();

        $response = $this->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function course_enrollment_requires_valid_course()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => 99999,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function user_cannot_enroll_twice()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();

        $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $response = $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function course_enrollment_increments_enrolled_count()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['enrolled_count' => 0]);

        $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $this->assertEquals(1, $course->refresh()->enrolled_count);
    }

    /** @test */
    public function user_can_view_enrollment_details()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(200);
        $response->assertJson(['id' => $enrollment->id]);
    }

    /** @test */
    public function user_can_view_their_enrollments()
    {
        $user = User::factory()->create();
        CourseEnrollment::factory()->count(3)->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->getJson('/api/enrollments');

        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public void test_user_can_view_lesson_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = CourseLesson::factory()->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/lessons/{$lesson->id}/progress");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_mark_lesson_complete()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lesson = CourseLesson::factory()->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->postJson("/api/lessons/{$lesson->id}/complete", []);

        $response->assertStatus(200);
        $this->assertDatabaseHas('course_progresses', [
            'user_id' => $user->id,
            'lesson_id' => $lesson->id,
            'is_completed' => true,
        ]);
    }

    /** @test */
    public function course_enrollment_tracks_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lessons = CourseLesson::factory()->count(4)->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        foreach ($lessons as $lesson) {
            $this->actingAs($user)->postJson("/api/lessons/{$lesson->id}/complete", []);
        }

        $enrollment->updateProgress();

        $this->assertEquals(100, $enrollment->refresh()->progress);
    }

    /** @test */
    public function incomplete_lessons_show_partial_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lessons = CourseLesson::factory()->count(4)->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $this->actingAs($user)->postJson("/api/lessons/{$lessons[0]->id}/complete", []);
        $this->actingAs($user)->postJson("/api/lessons/{$lessons[1]->id}/complete", []);

        $enrollment->updateProgress();

        $this->assertEquals(50, $enrollment->refresh()->progress);
    }

    /** @test */
    public function course_enrollment_marks_as_completed_at_100_percent()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lessons = CourseLesson::factory()->count(2)->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);

        foreach ($lessons as $lesson) {
            $this->actingAs($user)->postJson("/api/lessons/{$lesson->id}/complete", []);
        }

        $enrollment->updateProgress();

        $this->assertNotNull($enrollment->refresh()->completed_at);
    }

    /** @test */
    public function inactive_course_cannot_be_enrolled()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['is_active' => false]);

        $response = $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
            'payment_gateway' => 'fake',
        ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function course_enrollment_can_expire()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['access_days' => 30]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'expires_at' => now()->addDays(30),
        ]);

        $this->assertTrue($enrollment->isActive());
    }

    /** @test */
    public function expired_enrollment_is_not_active()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'expires_at' => now()->subDay(),
        ]);

        $this->assertFalse($enrollment->isActive());
    }

    /** @test */
    public function user_cannot_access_expired_enrollment()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'expires_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)->getJson("/api/enrollments/{$enrollment->id}");

        $response->assertStatus(403);
    }

    /** @test */
    public function user_can_get_certificate_upon_completion()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['certificate' => true]);
        $lessons = CourseLesson::factory()->count(2)->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'completed_at' => null,
        ]);

        foreach ($lessons as $lesson) {
            $this->actingAs($user)->postJson("/api/lessons/{$lesson->id}/complete", []);
        }

        $enrollment->updateProgress();

        $response = $this->actingAs($user)->getJson("/api/enrollments/{$enrollment->id}/certificate");

        $response->assertStatus(200);
    }

    /** @test */
    public function user_cannot_get_certificate_before_completion()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['certificate' => true]);
        CourseLesson::factory()->count(2)->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'progress' => 50,
        ]);

        $response = $this->actingAs($user)->getJson("/api/enrollments/{$enrollment->id}/certificate");

        $response->assertStatus(403);
    }

    /** @test */
    public function course_enrollment_returns_order_details()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create(['price' => 299000]);

        $response = $this->actingAs($user)->postJson('/api/courses/enroll', [
            'course_id' => $course->id,
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
    public function user_can_view_course_lessons_progress()
    {
        $user = User::factory()->create();
        $course = Course::factory()->create();
        $lessons = CourseLesson::factory()->count(3)->create(['course_id' => $course->id]);
        $enrollment = CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $response = $this->actingAs($user)->getJson("/api/enrollments/{$enrollment->id}/lessons");

        $response->assertStatus(200);
    }
}
