<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display a listing of products
     */
    public function index(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 12);
        $sortBy = request()->input('sort_by', 'latest');
        $categoryId = request()->input('category_id');
        $minPrice = request()->input('min_price');
        $maxPrice = request()->input('max_price');

        $query = Product::active()->inStock();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($minPrice) {
            $query->where('price', '>=', $minPrice);
        }

        if ($maxPrice) {
            $query->where('price', '<=', $maxPrice);
        }

        $products = match ($sortBy) {
            'popular' => $query->orderBy('sold_count', 'desc'),
            'price_asc' => $query->orderByRaw('COALESCE(special_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(special_price, price) DESC'),
            default => $query->latest(),
        };

        $products = $products->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.products.index', [
            'products' => $products,
            'sortBy' => $sortBy,
            'categoryId' => $categoryId,
            'minPrice' => $minPrice,
            'maxPrice' => $maxPrice,
        ]);
    }

    /**
     * Display a specific product
     */
    public function show(Product $product): View
    {
        if (!$product->is_active || !$product->isInStock()) {
            abort(404);
        }

        $relatedProducts = Product::active()
            ->inStock()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->limit(4)
            ->get();

        return view('frontend.products.show', [
            'product' => $product,
            'relatedProducts' => $relatedProducts,
        ]);
    }

    /**
     * Add product to cart
     */
    public function addToCart(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        if (!$product->is_active || !$product->isInStock()) {
            return response()->json([
                'success' => false,
                'message' => 'Product is not available',
            ], 422);
        }

        if ($request->quantity > $product->stock) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient stock. Available: ' . $product->stock,
            ], 422);
        }

        try {
            // Get cart from session
            $cart = session()->get('cart', []);

            $cartKey = 'product_' . $product->id;

            if (isset($cart[$cartKey])) {
                $cart[$cartKey]['quantity'] += $request->quantity;
            } else {
                $cart[$cartKey] = [
                    'type' => 'product',
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => $product->getCurrentPrice(),
                    'quantity' => $request->quantity,
                    'image' => $product->image,
                ];
            }

            session()->put('cart', $cart);

            return response()->json([
                'success' => true,
                'message' => 'Product added to cart',
                'cart_count' => count($cart),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add product to cart',
            ], 422);
        }
    }

    /**
     * Get cart
     */
    public function getCart(): JsonResponse
    {
        $cart = session()->get('cart', []);

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        return response()->json([
            'success' => true,
            'cart' => $cart,
            'count' => count($cart),
            'total' => $total,
        ]);
    }

    /**
     * Remove from cart
     */
    public function removeFromCart(Request $request): JsonResponse
    {
        $request->validate([
            'cart_key' => 'required|string',
        ]);

        $cart = session()->get('cart', []);

        if (isset($cart[$request->cart_key])) {
            unset($cart[$request->cart_key]);
            session()->put('cart', $cart);
        }

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
            'cart_count' => count($cart),
        ]);
    }

    /**
     * Clear cart
     */
    public function clearCart(): JsonResponse
    {
        session()->forget('cart');

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared',
        ]);
    }
}
