<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use App\Models\Product;
use App\Models\Service;
use App\Models\File;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WishlistController extends Controller
{
    /**
     * Display user's wishlist
     */
    public function index()
    {
        $wishlists = Wishlist::where('user_id', auth()->id())
            ->with('wishlistable')
            ->latest()
            ->paginate(20);

        return view('frontend.wishlist.index', compact('wishlists'));
    }

    /**
     * Add item to wishlist
     */
    public function add(Request $request)
    {
        $request->validate([
            'type' => 'required|in:product,service,file,course',
            'id' => 'required|integer',
        ]);

        try {
            // Get the item based on type
            $item = $this->getItemByType($request->type, $request->id);

            if (!$item) {
                return back()->with('error', 'Item not found.');
            }

            // Check if item already exists in wishlist
            $exists = Wishlist::where('user_id', auth()->id())
                ->where('wishlistable_type', get_class($item))
                ->where('wishlistable_id', $item->id)
                ->exists();

            if ($exists) {
                return back()->with('info', 'Item already in your wishlist!');
            }

            // Add to wishlist
            Wishlist::create([
                'user_id' => auth()->id(),
                'wishlistable_type' => get_class($item),
                'wishlistable_id' => $item->id,
            ]);

            Log::info('Item added to wishlist', [
                'user_id' => auth()->id(),
                'item_type' => get_class($item),
                'item_id' => $item->id,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Added to wishlist successfully!',
                ]);
            }

            return back()->with('success', 'Added to wishlist successfully!');
        } catch (\Exception $e) {
            Log::error('Error adding item to wishlist: ' . $e->getMessage());

            if ($request->expectsJson()) {
                return response()->json(['message' => 'Error adding to wishlist'], 500);
            }

            return back()->with('error', 'Error adding item to wishlist.');
        }
    }

    /**
     * Remove item from wishlist
     */
    public function remove($wishlistId)
    {
        try {
            $wishlist = Wishlist::where('id', $wishlistId)
                ->where('user_id', auth()->id())
                ->firstOrFail();

            $wishlist->delete();

            Log::info('Item removed from wishlist', [
                'user_id' => auth()->id(),
                'wishlist_id' => $wishlistId,
            ]);

            return back()->with('success', 'Removed from wishlist.');
        } catch (\Exception $e) {
            Log::error('Error removing item from wishlist: ' . $e->getMessage());
            return back()->with('error', 'Error removing item from wishlist.');
        }
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
