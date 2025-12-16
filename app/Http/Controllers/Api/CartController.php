<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\File;
use App\Models\Course;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Get or create cart for user/session
     */
    protected function getCart(Request $request)
    {
        if ($request->user()) {
            return Cart::firstOrCreate([
                'user_id' => $request->user()->id,
            ]);
        }

        return Cart::firstOrCreate([
            'session_id' => $request->session()->getId(),
        ]);
    }

    /**
     * Get cart items
     */
    public function index(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->load('items.cartable');

        return response()->json([
            'cart' => $cart,
            'items' => $cart->items,
            'subtotal' => $cart->subtotal,
            'tax' => $cart->tax,
            'total' => $cart->total,
            'total_items' => $cart->total_items,
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:product,service,file,course',
            'id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
            'options' => 'nullable|array',
        ]);

        $cart = $this->getCart($request);

        // Get the item model
        $model = match($validated['type']) {
            'product' => Product::find($validated['id']),
            'service' => Service::find($validated['id']),
            'file' => File::find($validated['id']),
            'course' => Course::find($validated['id']),
        };

        if (!$model) {
            return response()->json([
                'message' => 'Item not found',
            ], 404);
        }

        // Check if item is active
        if (!$model->is_active) {
            return response()->json([
                'message' => 'Item is not available',
            ], 400);
        }

        // Add to cart
        $quantity = $validated['quantity'] ?? 1;
        $options = $validated['options'] ?? [];

        $cartItem = $cart->addItem($model, $quantity, $options);

        $cart->load('items.cartable');

        return response()->json([
            'message' => 'Item added to cart',
            'cart_item' => $cartItem->load('cartable'),
            'cart' => [
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'total' => $cart->total,
                'total_items' => $cart->total_items,
            ],
        ], 201);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $itemId)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = $this->getCart($request);
        $item = $cart->updateItemQuantity($itemId, $validated['quantity']);

        if (!$item) {
            return response()->json([
                'message' => 'Item not found in cart',
            ], 404);
        }

        $cart->load('items.cartable');

        return response()->json([
            'message' => 'Cart updated',
            'cart_item' => $item->load('cartable'),
            'cart' => [
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'total' => $cart->total,
                'total_items' => $cart->total_items,
            ],
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(Request $request, $itemId)
    {
        $cart = $this->getCart($request);
        $removed = $cart->removeItem($itemId);

        if (!$removed) {
            return response()->json([
                'message' => 'Item not found in cart',
            ], 404);
        }

        $cart->load('items.cartable');

        return response()->json([
            'message' => 'Item removed from cart',
            'cart' => [
                'subtotal' => $cart->subtotal,
                'tax' => $cart->tax,
                'total' => $cart->total,
                'total_items' => $cart->total_items,
            ],
        ]);
    }

    /**
     * Clear cart
     */
    public function clear(Request $request)
    {
        $cart = $this->getCart($request);
        $cart->clear();

        return response()->json([
            'message' => 'Cart cleared',
            'cart' => [
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'total_items' => 0,
            ],
        ]);
    }
}
