<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    /**
     * Get user's orders
     */
    public function index(Request $request)
    {
        $query = Order::where('user_id', $request->user()->id)
            ->with('items');

        // Filtering
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $orders = $query->paginate($perPage);

        return response()->json([
            'orders' => $orders->items(),
            'pagination' => [
                'total' => $orders->total(),
                'per_page' => $orders->perPage(),
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
            ],
        ]);
    }

    /**
     * Get single order
     */
    public function show(Request $request, Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $order->load('items', 'payments');

        return response()->json([
            'order' => $order,
        ]);
    }

    /**
     * Create new order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.type' => 'required|in:product,service,file,course',
            'items.*.id' => 'required|integer',
            'items.*.quantity' => 'required|integer|min:1',
            'currency' => 'nullable|in:USD,VND,CNY',
            'notes' => 'nullable|string|max:500',
        ]);

        $user = $request->user();
        $totalAmount = 0;
        $orderItems = [];

        // Calculate total and prepare items
        foreach ($validated['items'] as $item) {
            $model = match($item['type']) {
                'product' => \App\Models\Product::find($item['id']),
                'service' => \App\Models\Service::find($item['id']),
                'file' => \App\Models\File::find($item['id']),
                'course' => \App\Models\Course::find($item['id']),
            };

            if (!$model) {
                return response()->json([
                    'message' => "Item {$item['type']} with ID {$item['id']} not found",
                ], 404);
            }

            $price = $model->price;
            $quantity = $item['quantity'];
            $subtotal = $price * $quantity;
            $totalAmount += $subtotal;

            $orderItems[] = [
                'orderable_type' => get_class($model),
                'orderable_id' => $model->id,
                'quantity' => $quantity,
                'price' => $price,
                'cost' => $model->cost ?? 0,
            ];
        }

        // Create order
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'total_amount' => $totalAmount,
            'currency' => $validated['currency'] ?? 'USD',
            'status' => 'pending',
            'payment_status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        // Create order items
        foreach ($orderItems as $itemData) {
            OrderItem::create(array_merge($itemData, ['order_id' => $order->id]));
        }

        $order->load('items');

        return response()->json([
            'message' => 'Order created successfully',
            'order' => $order,
        ], 201);
    }

    /**
     * Cancel order
     */
    public function cancel(Request $request, Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if order can be cancelled
        if (in_array($order->status, ['completed', 'cancelled'])) {
            return response()->json([
                'message' => 'Order cannot be cancelled',
            ], 400);
        }

        $order->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        return response()->json([
            'message' => 'Order cancelled successfully',
            'order' => $order,
        ]);
    }

    /**
     * Get order invoice
     */
    public function invoice(Request $request, Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // TODO: Generate PDF invoice
        return response()->json([
            'message' => 'Invoice available',
            'invoice_url' => url('/invoices/' . $order->id),
            'order' => $order,
        ]);
    }
}
