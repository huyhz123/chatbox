<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ServiceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of services
     */
    public function index(Request $request)
    {
        $query = Service::query()->with('category');

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

        // Pagination
        $services = $query->paginate(15);
        $categories = Category::where('type', 'service')->get();

        return view('admin.services.index', compact('services', 'categories'));
    }

    /**
     * Show the form for creating a new service
     */
    public function create()
    {
        $categories = Category::where('type', 'service')->get();
        return view('admin.services.create', compact('categories'));
    }

    /**
     * Store a newly created service
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:services,name',
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'api_provider' => 'nullable|string',
            'api_service_id' => 'nullable|string',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'processing_time' => 'nullable|string',
            'required_fields' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('services', 'public');
            }
            $validated['images'] = $images;
        }

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        $service = Service::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($service)
            ->log('created');

        return redirect()->route('admin.services.show', $service)
                       ->with('success', 'Service created successfully!');
    }

    /**
     * Display the specified service
     */
    public function show(Service $service)
    {
        return view('admin.services.show', compact('service'));
    }

    /**
     * Show the form for editing a service
     */
    public function edit(Service $service)
    {
        $categories = Category::where('type', 'service')->get();
        return view('admin.services.edit', compact('service', 'categories'));
    }

    /**
     * Update the specified service
     */
    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('services')->ignore($service->id)],
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'api_provider' => 'nullable|string',
            'api_service_id' => 'nullable|string',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'processing_time' => 'nullable|string',
            'required_fields' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($service->image) {
                \Storage::disk('public')->delete($service->image);
            }
            $validated['image'] = $request->file('image')->store('services', 'public');
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('services', 'public');
            }
            $validated['images'] = $images;
        }

        // Update slug if name changed
        $validated['slug'] = Str::slug($validated['name']);

        $service->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($service)
            ->log('updated');

        return redirect()->route('admin.services.show', $service)
                       ->with('success', 'Service updated successfully!');
    }

    /**
     * Remove the specified service
     */
    public function destroy(Service $service)
    {
        if ($service->image) {
            \Storage::disk('public')->delete($service->image);
        }

        if ($service->images) {
            foreach ($service->images as $image) {
                \Storage::disk('public')->delete($image);
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($service)
            ->log('deleted');

        $service->delete();

        return redirect()->route('admin.services.index')
                       ->with('success', 'Service deleted successfully!');
    }

    /**
     * Update service ordering
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*' => 'integer|exists:services,id',
        ]);

        foreach ($validated['items'] as $order => $serviceId) {
            Service::findOrFail($serviceId)->update(['order' => $order]);
        }

        return response()->json(['success' => true, 'message' => 'Order updated successfully']);
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Service $service)
    {
        $service->update(['is_featured' => !$service->is_featured]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($service)
            ->log('toggled featured');

        return redirect()->back()->with('success', 'Service featured status updated!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Service $service)
    {
        $service->update(['is_active' => !$service->is_active]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($service)
            ->log('toggled active');

        return redirect()->back()->with('success', 'Service status updated!');
    }
}
