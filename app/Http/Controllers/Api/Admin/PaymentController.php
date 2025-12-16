<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Get all payments (admin)
     */
    public function index(Request $request)
    {
        $query = Payment::with('order.user');

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('transaction_id', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($q) use ($search) {
                        $q->where('order_number', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by gateway
        if ($request->has('gateway')) {
            $query->where('payment_gateway', $request->gateway);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('created_at', '<=', $request->date_to);
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
    public function show(Payment $payment)
    {
        $payment->load('order.user');

        return response()->json([
            'payment' => $payment,
        ]);
    }

    /**
     * Update payment
     */
    public function update(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'nullable|in:pending,processing,completed,failed,refunded',
            'notes' => 'nullable|string|max:500',
        ]);

        $payment->update($validated);

        // Update order if payment completed
        if (isset($validated['status']) && $validated['status'] === 'completed') {
            $payment->order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        }

        return response()->json([
            'message' => 'Payment updated successfully',
            'payment' => $payment,
        ]);
    }

    /**
     * Delete payment
     */
    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json([
            'message' => 'Payment deleted successfully',
        ]);
    }

    /**
     * Update payment status
     */
    public function updateStatus(Request $request, Payment $payment)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,completed,failed,refunded',
        ]);

        $payment->update(['status' => $validated['status']]);

        // Update order accordingly
        if ($validated['status'] === 'completed') {
            $payment->order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
            ]);
        } elseif ($validated['status'] === 'failed') {
            $payment->order->update([
                'payment_status' => 'failed',
            ]);
        } elseif ($validated['status'] === 'refunded') {
            $payment->order->update([
                'payment_status' => 'refunded',
                'status' => 'cancelled',
            ]);
        }

        return response()->json([
            'message' => 'Payment status updated successfully',
            'payment' => $payment->load('order'),
        ]);
    }
}
