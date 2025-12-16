<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::query();

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%");
        }

        // Filter by type
        if ($request->has('type') && $request->get('type')) {
            $query->where('type', $request->get('type'));
        }

        // Filter by status
        if ($request->has('status') && $request->get('status')) {
            $query->where('is_active', $request->get('status') === 'active' ? true : false);
        }

        // Get parent categories only for main listing
        if (!$request->has('include_subcategories')) {
            $query->whereNull('parent_id');
        }

        // Ordering
        $categories = $query->orderBy('order')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $types = ['product', 'service', 'file', 'course'];
        $parentCategories = Category::whereNull('parent_id')->get();

        return view('admin.categories.create', compact('types', 'parentCategories'));
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
            'description' => 'nullable|string',
            'type' => 'required|in:product,service,file,course',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        $category = Category::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($category)
            ->log('created');

        return redirect()->route('admin.categories.show', $category)
                       ->with('success', 'Category created successfully!');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['children', 'parent']);
        $children = $category->children()->orderBy('order')->get();

        return view('admin.categories.show', compact('category', 'children'));
    }

    /**
     * Show the form for editing a category
     */
    public function edit(Category $category)
    {
        $types = ['product', 'service', 'file', 'course'];
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->get();

        return view('admin.categories.edit', compact('category', 'types', 'parentCategories'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
            'description' => 'nullable|string',
            'type' => 'required|in:product,service,file,course',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($category->image) {
                \Storage::disk('public')->delete($category->image);
            }
            $validated['image'] = $request->file('image')->store('categories', 'public');
        }

        // Update slug if name changed
        $validated['slug'] = Str::slug($validated['name']);

        $category->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($category)
            ->log('updated');

        return redirect()->route('admin.categories.show', $category)
                       ->with('success', 'Category updated successfully!');
    }

    /**
     * Remove the specified category
     */
    public function destroy(Category $category)
    {
        // Check if category has children or products
        if ($category->children()->exists() || $category->products()->exists()
            || $category->services()->exists() || $category->files()->exists()
            || $category->courses()->exists()) {
            return redirect()->back()
                           ->with('error', 'Cannot delete category with related items. Please remove related items first.');
        }

        if ($category->image) {
            \Storage::disk('public')->delete($category->image);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($category)
            ->log('deleted');

        $category->delete();

        return redirect()->route('admin.categories.index')
                       ->with('success', 'Category deleted successfully!');
    }

    /**
     * Update category ordering (drag-drop)
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:categories,id',
            'items.*.order' => 'required|integer|min:0',
            'items.*.parent_id' => 'nullable|integer|exists:categories,id',
        ]);

        foreach ($validated['items'] as $item) {
            Category::findOrFail($item['id'])->update([
                'order' => $item['order'],
                'parent_id' => $item['parent_id'] ?? null,
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->log('categories reordered');

        return response()->json(['success' => true, 'message' => 'Categories reordered successfully']);
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Category $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($category)
            ->log('status toggled');

        return redirect()->back()->with('success', 'Category status updated!');
    }

    /**
     * Get categories as tree (for nested display)
     */
    public function getTree(Request $request)
    {
        $type = $request->get('type');
        $query = Category::whereNull('parent_id')->orderBy('order');

        if ($type) {
            $query->where('type', $type);
        }

        $categories = $query->get();

        return response()->json($this->buildTree($categories));
    }

    /**
     * Build category tree structure
     */
    private function buildTree($categories)
    {
        $tree = [];

        foreach ($categories as $category) {
            $node = [
                'id' => $category->id,
                'name' => $category->name,
                'order' => $category->order,
                'type' => $category->type,
                'is_active' => $category->is_active,
                'children' => $this->buildTree($category->children),
            ];
            $tree[] = $node;
        }

        return $tree;
    }
}
