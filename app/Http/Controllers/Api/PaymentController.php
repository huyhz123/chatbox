<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Get user's payments
     */
    public function index(Request $request)
    {
        $query = Payment::whereHas('order', function ($q) use ($request) {
            $q->where('user_id', $request->user()->id);
        })->with('order');

        // Filtering
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('gateway')) {
            $query->where('payment_gateway', $request->gateway);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $payments = $query->paginate($perPage);

        return response()->json([
            'payments' => $payments->items(),
            'pagination' => [
                'total' => $payments->total(),
                'per_page' => $payments->perPage(),
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
            ],
        ]);
    }

    /**
     * Get single payment
     */
    public function show(Request $request, Payment $payment)
    {
        // Ensure user owns this payment
        if ($payment->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $payment->load('order');

        return response()->json([
            'payment' => $payment,
        ]);
    }

    /**
     * Create payment for order
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_gateway' => 'required|in:vnpay,momo,zalopay,stripe,paypal,usdt,fake',
            'return_url' => 'nullable|url',
        ]);

        $order = Order::findOrFail($validated['order_id']);

        // Ensure user owns this order
        if ($order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if order already paid
        if ($order->payment_status === 'paid') {
            return response()->json([
                'message' => 'Order already paid',
            ], 400);
        }

        try {
            $paymentData = [
                'return_url' => $validated['return_url'] ?? url('/api/payments/callback'),
            ];

            $result = $this->paymentService->createPayment(
                $order,
                $validated['payment_gateway'],
                $paymentData
            );

            return response()->json([
                'message' => 'Payment created successfully',
                'payment' => $result['payment'],
                'payment_url' => $result['payment_url'] ?? null,
                'redirect_url' => $result['payment_url'] ?? null,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry failed payment
     */
    public function retry(Request $request, Payment $payment)
    {
        // Ensure user owns this payment
        if ($payment->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Check if payment can be retried
        if ($payment->status !== 'failed') {
            return response()->json([
                'message' => 'Only failed payments can be retried',
            ], 400);
        }

        try {
            $result = $this->paymentService->createPayment(
                $payment->order,
                $payment->payment_gateway
            );

            return response()->json([
                'message' => 'Payment retry initiated',
                'payment' => $result['payment'],
                'payment_url' => $result['payment_url'] ?? null,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retry payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Request refund
     */
    public function refund(Request $request, Payment $payment)
    {
        // Ensure user owns this payment
        if ($payment->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        // Check if payment can be refunded
        if ($payment->status !== 'completed') {
            return response()->json([
                'message' => 'Only completed payments can be refunded',
            ], 400);
        }

        // Update payment status
        $payment->update([
            'status' => 'refund_requested',
            'refund_reason' => $request->reason,
        ]);

        return response()->json([
            'message' => 'Refund request submitted successfully',
            'payment' => $payment,
        ]);
    }

    /**
     * Get payment receipt
     */
    public function receipt(Request $request, Payment $payment)
    {
        // Ensure user owns this payment
        if ($payment->order->user_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // TODO: Generate PDF receipt
        return response()->json([
            'message' => 'Receipt available',
            'receipt_url' => url('/receipts/' . $payment->id),
            'payment' => $payment,
        ]);
    }
}
