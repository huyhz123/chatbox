<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Get all courses
     */
    public function index(Request $request)
    {
        $query = Course::with('category')->where('is_active', true);

        // Filtering
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('level')) {
            $query->where('level', $request->level);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $courses = $query->paginate($perPage);

        return response()->json([
            'courses' => $courses->items(),
            'pagination' => [
                'total' => $courses->total(),
                'per_page' => $courses->perPage(),
                'current_page' => $courses->currentPage(),
                'last_page' => $courses->lastPage(),
            ],
        ]);
    }

    /**
     * Get single course
     */
    public function show(Course $course)
    {
        $course->load('category', 'lessons');

        return response()->json([
            'course' => $course,
            'total_lessons' => $course->lessons->count(),
            'total_duration' => $course->lessons->sum('duration'),
        ]);
    }

    /**
     * Get course lessons
     */
    public function lessons(Course $course)
    {
        $lessons = $course->lessons()->orderBy('order')->get();

        return response()->json([
            'course' => $course->only(['id', 'name']),
            'lessons' => $lessons,
            'total_lessons' => $lessons->count(),
        ]);
    }

    /**
     * Get user's enrolled courses
     */
    public function myCourses(Request $request)
    {
        $enrollments = CourseEnrollment::where('user_id', $request->user()->id)
            ->with('course')
            ->get();

        return response()->json([
            'enrollments' => $enrollments,
            'total' => $enrollments->count(),
        ]);
    }

    /**
     * Enroll in course
     */
    public function enroll(Request $request, Course $course)
    {
        // Check if already enrolled
        $existing = CourseEnrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Already enrolled in this course',
                'enrollment' => $existing,
            ], 400);
        }

        // Create enrollment
        $enrollment = CourseEnrollment::create([
            'user_id' => $request->user()->id,
            'course_id' => $course->id,
            'status' => 'active',
            'progress' => 0,
        ]);

        return response()->json([
            'message' => 'Enrolled successfully',
            'enrollment' => $enrollment,
        ], 201);
    }

    /**
     * Get course progress
     */
    public function progress(Request $request, Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'Not enrolled in this course',
            ], 404);
        }

        return response()->json([
            'enrollment' => $enrollment,
            'progress' => $enrollment->progress,
            'status' => $enrollment->status,
        ]);
    }

    /**
     * Mark lesson as complete
     */
    public function completeLesson(Request $request, Course $course, CourseLesson $lesson)
    {
        $enrollment = CourseEnrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'Not enrolled in this course',
            ], 404);
        }

        // Update progress
        $totalLessons = $course->lessons->count();
        $completedLessons = $enrollment->completed_lessons + 1;
        $progress = ($completedLessons / $totalLessons) * 100;

        $enrollment->update([
            'completed_lessons' => $completedLessons,
            'progress' => round($progress, 2),
            'status' => $progress >= 100 ? 'completed' : 'active',
        ]);

        return response()->json([
            'message' => 'Lesson marked as complete',
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Get certificate (if course completed)
     */
    public function certificate(Request $request, Course $course)
    {
        $enrollment = CourseEnrollment::where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->where('status', 'completed')
            ->first();

        if (!$enrollment) {
            return response()->json([
                'message' => 'Course not completed or not enrolled',
            ], 404);
        }

        // TODO: Generate PDF certificate
        return response()->json([
            'message' => 'Certificate available',
            'enrollment' => $enrollment,
            'certificate_url' => url('/certificates/' . $enrollment->id),
        ]);
    }
}
