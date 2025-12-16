<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of files
     */
    public function index(Request $request)
    {
        $query = File::query()->with('category');

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

        // Filter by file type
        if ($request->has('file_type') && $request->get('file_type')) {
            $query->where('file_type', $request->get('file_type'));
        }

        // Pagination
        $files = $query->paginate(15);
        $categories = Category::where('type', 'file')->get();

        return view('admin.files.index', compact('files', 'categories'));
    }

    /**
     * Show the form for creating a new file
     */
    public function create()
    {
        $categories = Category::where('type', 'file')->get();
        return view('admin.files.create', compact('categories'));
    }

    /**
     * Store a newly created file
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:files,name',
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'version' => 'nullable|string',
            'download_limit' => 'nullable|integer|min:0',
            'is_encrypted' => 'boolean',
            'preview_url' => 'nullable|url',
            'file' => 'required|file|max:512000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $validated['file_path'] = $file->store('files', 'private');
            $validated['file_type'] = $file->getClientOriginalExtension();
            $validated['file_size'] = $file->getSize();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('files', 'public');
        }

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        $file = File::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($file)
            ->log('created');

        return redirect()->route('admin.files.show', $file)
                       ->with('success', 'File created successfully!');
    }

    /**
     * Display the specified file
     */
    public function show(File $file)
    {
        return view('admin.files.show', compact('file'));
    }

    /**
     * Show the form for editing a file
     */
    public function edit(File $file)
    {
        $categories = Category::where('type', 'file')->get();
        return view('admin.files.edit', compact('file', 'categories'));
    }

    /**
     * Update the specified file
     */
    public function update(Request $request, File $file)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('files')->ignore($file->id)],
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'version' => 'nullable|string',
            'download_limit' => 'nullable|integer|min:0',
            'is_encrypted' => 'boolean',
            'preview_url' => 'nullable|url',
            'file' => 'nullable|file|max:512000',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle file upload
        if ($request->hasFile('file')) {
            if ($file->file_path) {
                \Storage::disk('private')->delete($file->file_path);
            }
            $uploadedFile = $request->file('file');
            $validated['file_path'] = $uploadedFile->store('files', 'private');
            $validated['file_type'] = $uploadedFile->getClientOriginalExtension();
            $validated['file_size'] = $uploadedFile->getSize();
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($file->image) {
                \Storage::disk('public')->delete($file->image);
            }
            $validated['image'] = $request->file('image')->store('files', 'public');
        }

        // Update slug if name changed
        $validated['slug'] = Str::slug($validated['name']);

        $file->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($file)
            ->log('updated');

        return redirect()->route('admin.files.show', $file)
                       ->with('success', 'File updated successfully!');
    }

    /**
     * Remove the specified file
     */
    public function destroy(File $file)
    {
        if ($file->file_path) {
            \Storage::disk('private')->delete($file->file_path);
        }

        if ($file->image) {
            \Storage::disk('public')->delete($file->image);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($file)
            ->log('deleted');

        $file->delete();

        return redirect()->route('admin.files.index')
                       ->with('success', 'File deleted successfully!');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(File $file)
    {
        $file->update(['is_featured' => !$file->is_featured]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($file)
            ->log('toggled featured');

        return redirect()->back()->with('success', 'File featured status updated!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(File $file)
    {
        $file->update(['is_active' => !$file->is_active]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($file)
            ->log('toggled active');

        return redirect()->back()->with('success', 'File status updated!');
    }

    /**
     * Reset download count
     */
    public function resetDownloadCount(File $file)
    {
        $file->update(['download_count' => 0]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($file)
            ->log('download count reset');

        return redirect()->back()->with('success', 'Download count reset successfully!');
    }
}
