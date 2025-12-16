<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CourseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of courses
     */
    public function index(Request $request)
    {
        $query = Course::query()->with('category');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }

        // Filter by category
        if ($request->has('category') && $request->get('category')) {
            $query->where('category_id', $request->get('category'));
        }

        // Filter by status
        if ($request->has('status') && $request->get('status')) {
            $query->where('is_active', $request->get('status') === 'active' ? true : false);
        }

        // Filter by level
        if ($request->has('level') && $request->get('level')) {
            $query->where('level', $request->get('level'));
        }

        // Pagination
        $courses = $query->paginate(15);
        $categories = Category::where('type', 'course')->get();

        return view('admin.courses.index', compact('courses', 'categories'));
    }

    /**
     * Show the form for creating a new course
     */
    public function create()
    {
        $categories = Category::where('type', 'course')->get();
        $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
        return view('admin.courses.create', compact('categories', 'levels'));
    }

    /**
     * Store a newly created course
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:courses,name',
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced,expert',
            'duration' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'access_days' => 'nullable|integer|min:0',
            'certificate' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('courses', 'public');
        }

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        $course = Course::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($course)
            ->log('created');

        return redirect()->route('admin.courses.show', $course)
                       ->with('success', 'Course created successfully!');
    }

    /**
     * Display the specified course
     */
    public function show(Course $course)
    {
        $course->load(['lessons', 'enrollments']);
        return view('admin.courses.show', compact('course'));
    }

    /**
     * Show the form for editing a course
     */
    public function edit(Course $course)
    {
        $categories = Category::where('type', 'course')->get();
        $levels = ['beginner', 'intermediate', 'advanced', 'expert'];
        return view('admin.courses.edit', compact('course', 'categories', 'levels'));
    }

    /**
     * Update the specified course
     */
    public function update(Request $request, Course $course)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('courses')->ignore($course->id)],
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'level' => 'required|in:beginner,intermediate,advanced,expert',
            'duration' => 'nullable|string',
            'instructor_name' => 'nullable|string|max:255',
            'access_days' => 'nullable|integer|min:0',
            'certificate' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($course->image) {
                \Storage::disk('public')->delete($course->image);
            }
            $validated['image'] = $request->file('image')->store('courses', 'public');
        }

        // Update slug if name changed
        $validated['slug'] = Str::slug($validated['name']);

        $course->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($course)
            ->log('updated');

        return redirect()->route('admin.courses.show', $course)
                       ->with('success', 'Course updated successfully!');
    }

    /**
     * Remove the specified course
     */
    public function destroy(Course $course)
    {
        if ($course->image) {
            \Storage::disk('public')->delete($course->image);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($course)
            ->log('deleted');

        $course->delete();

        return redirect()->route('admin.courses.index')
                       ->with('success', 'Course deleted successfully!');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Course $course)
    {
        $course->update(['is_featured' => !$course->is_featured]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($course)
            ->log('toggled featured');

        return redirect()->back()->with('success', 'Course featured status updated!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Course $course)
    {
        $course->update(['is_active' => !$course->is_active]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($course)
            ->log('toggled active');

        return redirect()->back()->with('success', 'Course status updated!');
    }
}
