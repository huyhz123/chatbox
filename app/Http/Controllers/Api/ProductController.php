<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products
     */
    public function index(Request $request)
    {
        $query = Product::with('category')->where('is_active', true);

        // Filtering
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        if ($request->has('in_stock')) {
            $query->where('stock', '>', 0);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $products = $query->paginate($perPage);

        return response()->json([
            'products' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'per_page' => $products->perPage(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'from' => $products->firstItem(),
                'to' => $products->lastItem(),
            ],
        ]);
    }

    /**
     * Get single product
     */
    public function show(Product $product)
    {
        $product->load('category');

        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Get products by category
     */
    public function byCategory(Category $category)
    {
        $products = Product::where('category_id', $category->id)
            ->where('is_active', true)
            ->with('category')
            ->paginate(15);

        return response()->json([
            'category' => $category,
            'products' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * Search products
     */
    public function search($query)
    {
        $products = Product::where('is_active', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('description', 'like', "%{$query}%")
                    ->orWhere('sku', 'like', "%{$query}%");
            })
            ->with('category')
            ->paginate(15);

        return response()->json([
            'query' => $query,
            'products' => $products->items(),
            'pagination' => [
                'total' => $products->total(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    }

    /**
     * Get product reviews (placeholder)
     */
    public function reviews(Product $product)
    {
        // TODO: Implement when Review model is created
        return response()->json([
            'product' => $product,
            'reviews' => [],
            'average_rating' => 0,
            'total_reviews' => 0,
        ]);
    }
}
