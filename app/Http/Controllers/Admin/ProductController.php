<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of products
     */
    public function index(Request $request)
    {
        $query = Product::query()->with('category');

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%")
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

        // Filter by stock status
        if ($request->has('stock_status') && $request->get('stock_status')) {
            $query->where('stock_status', $request->get('stock_status'));
        }

        // Pagination
        $products = $query->paginate(15);
        $categories = Category::where('type', 'product')->get();

        return view('admin.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new product
     */
    public function create()
    {
        $categories = Category::where('type', 'product')->get();
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created product
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:255|unique:products,name',
            'sku' => 'required|string|unique:products,sku',
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_alert' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('products', 'public');
            }
            $validated['images'] = $images;
        }

        // Generate slug
        $validated['slug'] = Str::slug($validated['name']);

        // Set stock status
        $validated['stock_status'] = $validated['stock'] > 0 ? 'in_stock' : 'out_of_stock';

        $product = Product::create($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->log('created');

        return redirect()->route('admin.products.show', $product)
                       ->with('success', 'Product created successfully!');
    }

    /**
     * Display the specified product
     */
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing a product
     */
    public function edit(Product $product)
    {
        $categories = Category::where('type', 'product')->get();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified product
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => ['required', 'string', 'max:255', Rule::unique('products')->ignore($product->id)],
            'sku' => ['required', 'string', Rule::unique('products')->ignore($product->id)],
            'description' => 'required|string',
            'details' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'special_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_alert' => 'required|integer|min:0',
            'weight' => 'nullable|numeric|min:0',
            'attributes' => 'nullable|array',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($product->image) {
                \Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        // Handle multiple images upload
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $images[] = $image->store('products', 'public');
            }
            $validated['images'] = $images;
        }

        // Update slug if name changed
        $validated['slug'] = Str::slug($validated['name']);

        // Update stock status
        $validated['stock_status'] = $validated['stock'] > 0 ? 'in_stock' : 'out_of_stock';

        $product->update($validated);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->log('updated');

        return redirect()->route('admin.products.show', $product)
                       ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product)
    {
        if ($product->image) {
            \Storage::disk('public')->delete($product->image);
        }

        if ($product->images) {
            foreach ($product->images as $image) {
                \Storage::disk('public')->delete($image);
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->log('deleted');

        $product->delete();

        return redirect()->route('admin.products.index')
                       ->with('success', 'Product deleted successfully!');
    }

    /**
     * Adjust stock for a product
     */
    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer',
            'adjustment_type' => 'required|in:add,subtract',
            'notes' => 'nullable|string',
        ]);

        $previousStock = $product->stock;

        if ($validated['adjustment_type'] === 'add') {
            $product->increaseStock($validated['quantity']);
        } else {
            $product->decreaseStock($validated['quantity']);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->withProperties([
                'previous_stock' => $previousStock,
                'new_stock' => $product->stock,
                'adjustment_type' => $validated['adjustment_type'],
                'quantity' => $validated['quantity'],
                'notes' => $validated['notes'] ?? null,
            ])
            ->log('stock adjusted');

        return redirect()->back()->with('success', 'Stock adjusted successfully!');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(Product $product)
    {
        $product->update(['is_featured' => !$product->is_featured]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->log('toggled featured');

        return redirect()->back()->with('success', 'Product featured status updated!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive(Product $product)
    {
        $product->update(['is_active' => !$product->is_active]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($product)
            ->log('toggled active');

        return redirect()->back()->with('success', 'Product status updated!');
    }
}
