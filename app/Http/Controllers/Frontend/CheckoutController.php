<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    /**
     * Create a new instance of the controller
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display checkout page
     */
    public function show(Order $order): View
    {
        // Verify user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Verify order is pending
        if ($order->status !== 'pending' || $order->payment_status !== 'pending') {
            abort(422, 'This order cannot be checked out');
        }

        $items = $order->items()->with('itemable')->get();

        $paymentMethods = [
            'vnpay' => 'VNPay',
            'momo' => 'Momo',
            'zalopay' => 'ZaloPay',
            'stripe' => 'Stripe',
            'paypal' => 'PayPal',
            'usdt' => 'USDT',
            'fake' => 'Fake Payment (Demo)',
        ];

        // Filter available payment methods based on config
        $availablePaymentMethods = array_filter($paymentMethods, function ($key) {
            return config('payment.gateways.' . $key . '.enabled', false);
        }, ARRAY_FILTER_USE_KEY);

        return view('frontend.checkout.show', [
            'order' => $order,
            'items' => $items,
            'paymentMethods' => $availablePaymentMethods,
        ]);
    }

    /**
     * Process checkout
     */
    public function process(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Verify user owns this order
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        // Verify order is pending
        if ($order->status !== 'pending' || $order->payment_status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'This order cannot be checked out',
            ], 422);
        }

        try {
            // Update order with customer information
            $order->update([
                'customer_name' => $request->customer_name,
                'customer_email' => $request->customer_email,
                'customer_phone' => $request->customer_phone,
                'customer_address' => $request->customer_address,
                'notes' => $request->notes,
                'payment_method' => $request->payment_method,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Checkout processed',
                'order_id' => $order->id,
                'redirect' => route('payment.select', $order->id),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to process checkout: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Apply coupon/discount
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'coupon_code' => 'required|string',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Verify user owns this order
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            // TODO: Implement coupon validation logic
            // For now, just return error
            return response()->json([
                'success' => false,
                'message' => 'Invalid coupon code',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Update order shipping address
     */
    public function updateAddress(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'customer_name' => 'required|string|max:255',
            'customer_email' => 'required|email',
            'customer_phone' => 'required|string|max:20',
            'customer_address' => 'required|string|max:500',
        ]);

        $order = Order::findOrFail($request->order_id);

        // Verify user owns this order
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $order->update($request->only([
                'customer_name',
                'customer_email',
                'customer_phone',
                'customer_address',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Address updated',
                'order' => $order,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address: ' . $e->getMessage(),
            ], 422);
        }
    }
}
