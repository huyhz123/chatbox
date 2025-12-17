<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Service;
use App\Models\File;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    /**
     * Display the shopping cart
     */
    public function index()
    {
        $cart = $this->getCart();

        return view('frontend.cart.index', compact('cart'));
    }

    /**
     * Add item to cart
     */
    public function add(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,service,file,course',
            'id' => 'required|integer',
            'quantity' => 'nullable|integer|min:1',
        ]);

        try {
            DB::beginTransaction();

            // Get or create cart
            $cart = $this->getOrCreateCart();

            // Get the item based on type
            $item = $this->getItemByType($request->type, $request->id);

            if (!$item) {
                return back()->with('error', 'Item not found.');
            }

            // Check if item already exists in cart
            $existingItem = $cart->items()
                ->where('cartable_type', get_class($item))
                ->where('cartable_id', $item->id)
                ->first();

            if ($existingItem) {
                // Update quantity
                $existingItem->quantity += $request->quantity ?? 1;
                $existingItem->save();
            } else {
                // Add new item
                $cart->items()->create([
                    'cartable_type' => get_class($item),
                    'cartable_id' => $item->id,
                    'quantity' => $request->quantity ?? 1,
                    'price' => $item->price,
                    'options' => $request->options ?? null,
                ]);
            }

            DB::commit();

            return back()->with('success', 'Item added to cart successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error adding item to cart: ' . $e->getMessage());
            return back()->with('error', 'Error adding item to cart.');
        }
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, $itemId)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);

        try {
            $cart = $this->getCart();

            if (!$cart) {
                return back()->with('error', 'Cart not found.');
            }

            $item = $cart->items()->findOrFail($itemId);
            $item->quantity = $request->quantity;
            $item->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Cart updated successfully',
                    'subtotal' => $cart->fresh()->subtotal,
                    'total' => $cart->fresh()->total,
                ]);
            }

            return back()->with('success', 'Cart updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating cart: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Error updating cart'], 500);
            }

            return back()->with('error', 'Error updating cart.');
        }
    }

    /**
     * Remove item from cart
     */
    public function remove($itemId)
    {
        try {
            $cart = $this->getCart();

            if (!$cart) {
                return back()->with('error', 'Cart not found.');
            }

            $item = $cart->items()->findOrFail($itemId);
            $item->delete();

            return back()->with('success', 'Item removed from cart.');
        } catch (\Exception $e) {
            Log::error('Error removing item from cart: ' . $e->getMessage());
            return back()->with('error', 'Error removing item from cart.');
        }
    }

    /**
     * Clear entire cart
     */
    public function clear()
    {
        try {
            $cart = $this->getCart();

            if ($cart) {
                $cart->items()->delete();
            }

            return back()->with('success', 'Cart cleared successfully!');
        } catch (\Exception $e) {
            Log::error('Error clearing cart: ' . $e->getMessage());
            return back()->with('error', 'Error clearing cart.');
        }
    }

    /**
     * Get current cart (authenticated user or guest session)
     */
    protected function getCart()
    {
        if (auth()->check()) {
            return Cart::where('user_id', auth()->id())->first();
        }

        return Cart::where('session_id', session()->getId())->first();
    }

    /**
     * Get or create cart
     */
    protected function getOrCreateCart()
    {
        if (auth()->check()) {
            return Cart::firstOrCreate([
                'user_id' => auth()->id(),
            ]);
        }

        return Cart::firstOrCreate([
            'session_id' => session()->getId(),
        ]);
    }

    /**
     * Get item by type and ID
     */
    protected function getItemByType(string $type, int $id)
    {
        return match($type) {
            'product' => Product::find($id),
            'service' => Service::find($id),
            'file' => File::find($id),
            'course' => Course::find($id),
            default => null,
        };
    }
}
