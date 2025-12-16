<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ServiceController extends Controller
{
    /**
     * Display a listing of services
     */
    public function index(): View
    {
        $page = request()->input('page', 1);
        $perPage = request()->input('per_page', 12);
        $sortBy = request()->input('sort_by', 'latest');
        $categoryId = request()->input('category_id');

        $query = Service::active();

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $services = match ($sortBy) {
            'popular' => $query->orderBy('sold_count', 'desc'),
            'price_asc' => $query->orderByRaw('COALESCE(special_price, price) ASC'),
            'price_desc' => $query->orderByRaw('COALESCE(special_price, price) DESC'),
            default => $query->latest(),
        };

        $services = $services->paginate($perPage, ['*'], 'page', $page);

        return view('frontend.services.index', [
            'services' => $services,
            'sortBy' => $sortBy,
            'categoryId' => $categoryId,
        ]);
    }

    /**
     * Display a specific service
     */
    public function show(Service $service): View
    {
        if (!$service->is_active) {
            abort(404);
        }

        $relatedServices = Service::active()
            ->where('category_id', $service->category_id)
            ->where('id', '!=', $service->id)
            ->limit(4)
            ->get();

        return view('frontend.services.show', [
            'service' => $service,
            'relatedServices' => $relatedServices,
        ]);
    }

    /**
     * Submit a service order
     */
    public function submitOrder(Request $request): JsonResponse
    {
        $request->validate([
            'service_id' => 'required|exists:services,id',
            'quantity' => 'required|integer|min:' . $this->getService($request->service_id)->min_quantity . '|max:' . $this->getService($request->service_id)->max_quantity,
            'options' => 'nullable|array',
        ]);

        $service = Service::findOrFail($request->service_id);

        if (!$service->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'This service is not available',
            ], 422);
        }

        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please login to place an order',
                    'redirect' => route('login'),
                ], 401);
            }

            // Create order
            $order = Order::create([
                'order_number' => 'ORD-' . time() . '-' . random_int(1000, 9999),
                'user_id' => $user->id,
                'type' => 'service',
                'status' => 'pending',
                'payment_status' => 'pending',
                'currency' => config('app.currency', 'VND'),
                'customer_name' => $user->name,
                'customer_email' => $user->email,
                'customer_phone' => $user->phone,
                'ip_address' => $request->ip(),
            ]);

            // Add order item
            $price = $service->getCurrentPrice();
            $subtotal = $price * $request->quantity;

            OrderItem::create([
                'order_id' => $order->id,
                'itemable_type' => Service::class,
                'itemable_id' => $service->id,
                'quantity' => $request->quantity,
                'price' => $price,
                'options' => $request->options ?? [],
            ]);

            // Update order totals
            $order->update([
                'subtotal' => $subtotal,
                'total' => $subtotal,
            ]);

            $service->incrementSoldCount($request->quantity);

            return response()->json([
                'success' => true,
                'message' => 'Order placed successfully',
                'order_id' => $order->id,
                'redirect' => route('checkout.show', $order->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to place order: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get service details
     */
    private function getService($serviceId): ?Service
    {
        return Service::find($serviceId);
    }
}
