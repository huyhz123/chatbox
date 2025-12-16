<?php

namespace App\Services;

use App\Models\Course;
use App\Models\User;
use App\Models\Order;
use App\Models\CourseEnrollment;
use Carbon\Carbon;

class CourseService
{
    public function enrollUser(User $user, Course $course, Order $order)
    {
        $enrollment = CourseEnrollment::create([
            'course_id' => $course->id,
            'user_id' => $user->id,
            'order_id' => $order->id,
            'enrolled_at' => now(),
            'expires_at' => $course->access_days > 0
                ? now()->addDays($course->access_days)
                : null,
            'progress' => 0,
        ]);

        // Create progress records for all lessons
        foreach ($course->lessons as $lesson) {
            $enrollment->lessonProgress()->create([
                'lesson_id' => $lesson->id,
                'is_completed' => false,
            ]);
        }

        // Increment enrolled count
        $course->incrementEnrolledCount();

        // Send notification
        app(NotificationService::class)->sendCourseEnrolled($user, $course);

        return $enrollment;
    }

    public function isUserEnrolled(User $user, Course $course)
    {
        return CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->active()
            ->exists();
    }

    public function getUserEnrollment(User $user, Course $course)
    {
        return CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->active()
            ->first();
    }

    public function markLessonComplete(CourseEnrollment $enrollment, $lessonId)
    {
        $progress = $enrollment->lessonProgress()
            ->where('lesson_id', $lessonId)
            ->first();

        if ($progress && !$progress->is_completed) {
            $progress->markAsCompleted();
            return true;
        }

        return false;
    }
}
