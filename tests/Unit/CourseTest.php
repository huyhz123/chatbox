<?php

namespace Tests\Unit;

use App\Models\Course;
use App\Models\Category;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function course_can_be_created()
    {
        $course = Course::factory()->create([
            'name' => 'Laravel Basics',
            'slug' => 'laravel-basics',
        ]);

        $this->assertDatabaseHas('courses', [
            'id' => $course->id,
            'name' => 'Laravel Basics',
        ]);
    }

    /** @test */
    public function course_has_required_attributes()
    {
        $course = Course::factory()->create([
            'name' => 'Advanced Laravel',
            'price' => 299000,
            'level' => 'advanced',
            'duration' => 120,
            'instructor_name' => 'John Doe',
            'is_active' => true,
        ]);

        $this->assertEquals('Advanced Laravel', $course->name);
        $this->assertEquals(299000, $course->price);
        $this->assertEquals('advanced', $course->level);
        $this->assertEquals(120, $course->duration);
        $this->assertEquals('John Doe', $course->instructor_name);
        $this->assertTrue($course->is_active);
    }

    /** @test */
    public function active_scope_filters_active_courses()
    {
        Course::factory()->create(['is_active' => true]);
        Course::factory()->create(['is_active' => true]);
        Course::factory()->create(['is_active' => false]);

        $activeCourses = Course::active()->get();

        $this->assertEquals(2, $activeCourses->count());
    }

    /** @test */
    public function featured_scope_filters_featured_courses()
    {
        Course::factory()->create(['is_featured' => true]);
        Course::factory()->create(['is_featured' => false]);

        $featuredCourses = Course::featured()->get();

        $this->assertEquals(1, $featuredCourses->count());
        $this->assertTrue($featuredCourses->first()->is_featured);
    }

    /** @test */
    public function by_level_scope_filters_by_level()
    {
        Course::factory()->create(['level' => 'beginner']);
        Course::factory()->create(['level' => 'intermediate']);
        Course::factory()->create(['level' => 'advanced']);
        Course::factory()->create(['level' => 'beginner']);

        $beginnerCourses = Course::byLevel('beginner')->get();

        $this->assertEquals(2, $beginnerCourses->count());
    }

    /** @test */
    public function get_current_price_returns_special_price_if_available()
    {
        $course = Course::factory()->create([
            'price' => 500000,
            'special_price' => 399000,
        ]);

        $this->assertEquals(399000, $course->getCurrentPrice());
    }

    /** @test */
    public function get_current_price_returns_regular_price_if_no_special_price()
    {
        $course = Course::factory()->create([
            'price' => 500000,
            'special_price' => null,
        ]);

        $this->assertEquals(500000, $course->getCurrentPrice());
    }

    /** @test */
    public function increment_enrolled_count_increases_count()
    {
        $course = Course::factory()->create(['enrolled_count' => 10]);

        $course->incrementEnrolledCount();

        $this->assertEquals(11, $course->refresh()->enrolled_count);
    }

    /** @test */
    public function get_total_lessons_count_returns_zero_for_new_course()
    {
        $course = Course::factory()->create();

        $this->assertEquals(0, $course->getTotalLessonsCount());
    }

    /** @test */
    public function get_total_lessons_count_returns_lesson_count()
    {
        $course = Course::factory()->create();
        CourseLesson::factory()->count(5)->create(['course_id' => $course->id]);

        $this->assertEquals(5, $course->getTotalLessonsCount());
    }

    /** @test */
    public function course_belongs_to_category()
    {
        $category = Category::factory()->create();
        $course = Course::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $course->category);
        $this->assertEquals($category->id, $course->category->id);
    }

    /** @test */
    public function course_has_many_lessons()
    {
        $course = Course::factory()->create();
        CourseLesson::factory()->count(3)->create(['course_id' => $course->id]);

        $this->assertEquals(3, $course->lessons()->count());
    }

    /** @test */
    public function course_has_many_enrollments()
    {
        $course = Course::factory()->create();

        $this->assertEquals(0, $course->enrollments()->count());
    }

    /** @test */
    public function course_can_have_order_items()
    {
        $course = Course::factory()->create();

        $this->assertEquals(0, $course->orderItems()->count());
    }

    /** @test */
    public function course_soft_delete_works()
    {
        $course = Course::factory()->create();
        $courseId = $course->id;

        $course->delete();

        $this->assertNull(Course::find($courseId));
        $this->assertNotNull(Course::withTrashed()->find($courseId));
    }

    /** @test */
    public function course_price_is_cast_to_decimal()
    {
        $course = Course::factory()->create(['price' => '299.99']);

        $this->assertIsNumeric($course->price);
    }

    /** @test */
    public function active_and_featured_scopes_can_be_chained()
    {
        Course::factory()->create(['is_active' => true, 'is_featured' => true]);
        Course::factory()->create(['is_active' => true, 'is_featured' => false]);
        Course::factory()->create(['is_active' => false, 'is_featured' => true]);

        $courses = Course::active()->featured()->get();

        $this->assertEquals(1, $courses->count());
    }

    /** @test */
    public function active_and_by_level_scopes_can_be_chained()
    {
        Course::factory()->create(['is_active' => true, 'level' => 'beginner']);
        Course::factory()->create(['is_active' => true, 'level' => 'advanced']);
        Course::factory()->create(['is_active' => false, 'level' => 'beginner']);

        $courses = Course::active()->byLevel('beginner')->get();

        $this->assertEquals(1, $courses->count());
    }

    /** @test */
    public function course_has_certificate_option()
    {
        $course = Course::factory()->create([
            'certificate' => true,
        ]);

        $this->assertTrue($course->certificate);
    }

    /** @test */
    public function course_has_access_days_limit()
    {
        $course = Course::factory()->create([
            'access_days' => 90,
        ]);

        $this->assertEquals(90, $course->access_days);
    }

    /** @test */
    public function course_track_enrolled_count()
    {
        $course = Course::factory()->create(['enrolled_count' => 0]);

        $course->incrementEnrolledCount();
        $course->incrementEnrolledCount();
        $course->incrementEnrolledCount();

        $this->assertEquals(3, $course->refresh()->enrolled_count);
    }

    /** @test */
    public function multiple_courses_increment_enrolled_count_independently()
    {
        $course1 = Course::factory()->create(['enrolled_count' => 5]);
        $course2 = Course::factory()->create(['enrolled_count' => 10]);

        $course1->incrementEnrolledCount();
        $course2->incrementEnrolledCount();

        $this->assertEquals(6, $course1->refresh()->enrolled_count);
        $this->assertEquals(11, $course2->refresh()->enrolled_count);
    }

    /** @test */
    public function course_lesson_count_with_multiple_courses()
    {
        $course1 = Course::factory()->create();
        $course2 = Course::factory()->create();

        CourseLesson::factory()->count(3)->create(['course_id' => $course1->id]);
        CourseLesson::factory()->count(5)->create(['course_id' => $course2->id]);

        $this->assertEquals(3, $course1->getTotalLessonsCount());
        $this->assertEquals(5, $course2->getTotalLessonsCount());
    }

    /** @test */
    public function course_soft_deleted_not_returned_by_active_scope()
    {
        $course = Course::factory()->create(['is_active' => true]);

        $course->delete();

        $activeCourses = Course::active()->get();

        $this->assertEquals(0, $activeCourses->count());
    }
}
