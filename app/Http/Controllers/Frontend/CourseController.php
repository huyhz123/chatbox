<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\CourseLesson;
use App\Models\CourseProgress;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CourseController extends Controller
{
    protected CourseService $courseService;

    public function __construct(CourseService $courseService)
    {
        $this->courseService = $courseService;
    }

    /**
     * Display a listing of courses
     */
    public function index(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 12);
        $sortBy = request()->input('sort_by', 'latest');
        $level = request()->input('level');
        $categoryId = request()->input('category_id');

        $query = Course::active();

        if ($level) {
            $query->byLevel($level);
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $courses = match ($sortBy) {
            'popular' => $query->orderBy('enrolled_count', 'desc'),
            'price_asc' => $query->orderByRaw('COALESCE(special_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(special_price, price) DESC'),
            default => $query->latest(),
        };

        $courses = $courses->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.courses.index', [
            'courses' => $courses,
            'sortBy' => $sortBy,
            'level' => $level,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Display a specific course
     */
    public function show(Course $course): View
    {
        if (!$course->is_active) {
            abort(404);
        }

        $lessons = $course->lessons()
            ->orderBy('order')
            ->get();

        $userEnrollment = null;
        $userProgress = null;

        if (auth()->check()) {
            $userEnrollment = $course->enrollments()
                ->where('user_id', auth()->id())
                ->first();

            if ($userEnrollment) {
                $userProgress = CourseProgress::where('user_id', auth()->id())
                    ->where('course_id', $course->id)
                    ->first();
            }
        }

        $relatedCourses = Course::active()
            ->where('category_id', $course->category_id)
            ->where('id', '!=', $course->id)
            ->limit(4)
            ->get();

        return view('frontend.courses.show', [
            'course' => $course,
            'lessons' => $lessons,
            'userEnrollment' => $userEnrollment,
            'userProgress' => $userProgress,
            'relatedCourses' => $relatedCourses,
        ]);
    }

    /**
     * Enroll in a course
     */
    public function enroll(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
        ]);

        $course = Course::findOrFail($request->course_id);

        if (!$course->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Course is not available',
            ], 422);
        }

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to enroll',
                    'redirect' => route('login'),
                ], 401);
            }

            // Check if already enrolled
            $existingEnrollment = $course->enrollments()
                ->where('user_id', $user->id)
                ->first();

            if ($existingEnrollment) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already enrolled in this course',
                ], 422);
            }

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . time() . '-' . random_int(1000, 9999),
                'user_id' => $user->id,
                'type' => 'course',
                'status' => 'pending',
                'payment_status' => 'pending',
                'currency' => config('app.currency', 'VND'),
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
                'ip_address' => $request->ip(),
            ]);

            // Add order item
            $price = $course->getCurrentPrice();

            OrderItem::create([
                'order_id' => $order->id,
                'itemable_type' => Course::class,
                'itemable_id' => $course->id,
                'quantity' => 1,
                'price' => $price,
                'options' => [],
            ]);

            // Update order totals
            $order->update([
                'subtotal' => $price,
                'total' => $price,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Proceeding to checkout',
                'order_id' => $order->id,
                'redirect' => route('checkout.show', $order->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to enroll: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get course lessons
     */
    public function lessons(Course $course): View
    {
        if (!$course->is_active) {
            abort(404);
        }

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Please login to view lessons');
        }

        $enrollment = $course->enrollments()
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this course');
        }

        $lessons = $course->lessons()
            ->orderBy('order')
            ->get();

        $progress = CourseProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        return view('frontend.courses.lessons', [
            'course' => $course,
            'lessons' => $lessons,
            'enrollment' => $enrollment,
            'progress' => $progress,
        ]);
    }

    /**
     * Get specific lesson
     */
    public function lesson(Course $course, CourseLesson $lesson): View
    {
        if (!$course->is_active) {
            abort(404);
        }

        if ($lesson->course_id !== $course->id) {
            abort(404);
        }

        $user = auth()->user();
        if (!$user) {
            abort(403, 'Please login to view lesson');
        }

        $enrollment = $course->enrollments()
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            abort(403, 'You are not enrolled in this course');
        }

        $lessons = $course->lessons()
            ->orderBy('order')
            ->get();

        return view('frontend.courses.lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'lessons' => $lessons,
            'enrollment' => $enrollment,
        ]);
    }

    /**
     * Mark lesson as complete
     */
    public function completeLesson(Request $request): JsonResponse
    {
        $request->validate([
            'course_id' => 'required|exists:courses,id',
            'lesson_id' => 'required|exists:course_lessons,id',
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $course = Course::findOrFail($request->course_id);
        $lesson = CourseLesson::findOrFail($request->lesson_id);

        if ($lesson->course_id !== $course->id) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid lesson',
            ], 422);
        }

        try {
            $this->courseService->markLessonComplete($user, $course, $lesson);

            return response()->json([
                'success' => true,
                'message' => 'Lesson marked as complete',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark lesson complete: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get course progress
     */
    public function progress(Course $course): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $enrollment = $course->enrollments()
            ->where('user_id', $user->id)
            ->first();

        if (!$enrollment) {
            return response()->json([
                'success' => false,
                'message' => 'Not enrolled',
            ], 422);
        }

        $progress = CourseProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        $completedLessons = $user->courseProgress()
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->count();

        $totalLessons = $course->getTotalLessonsCount();

        return response()->json([
            'success' => true,
            'completed_lessons' => $completedLessons,
            'total_lessons' => $totalLessons,
            'percentage' => $totalLessons > 0 ? ($completedLessons / $totalLessons) * 100 : 0,
            'progress' => $progress,
        ]);
    }
}
