<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin|superadmin');
    }

    /**
     * Display a listing of orders
     */
    public function index(Request $request)
    {
        $query = Order::query()->with(['user', 'items']);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
        }

        // Filter by status
        if ($request->has('status') && $request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        // Filter by payment status
        if ($request->has('payment_status') && $request->get('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        // Filter by date range
        if ($request->has('from_date') && $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date') && $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $orders = $query->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Display the specified order
     */
    public function show(Order $order)
    {
        $order->load(['user', 'items', 'payments', 'tickets']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Show the form for editing order status
     */
    public function edit(Order $order)
    {
        $statuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'];
        $paymentStatuses = ['pending', 'paid', 'failed', 'refunded'];

        return view('admin.orders.edit', compact('order', 'statuses', 'paymentStatuses'));
    }

    /**
     * Update order status
     */
    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
            'notes' => 'nullable|string',
        ]);

        $oldStatus = $order->status;
        $oldPaymentStatus = $order->payment_status;

        $order->update([
            'status' => $validated['status'],
            'payment_status' => $validated['payment_status'],
            'notes' => $validated['notes'] ?? $order->notes,
        ]);

        activity()
            ->causedBy(auth()->user())
            ->performedOn($order)
            ->withProperties([
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'old_payment_status' => $oldPaymentStatus,
                'new_payment_status' => $validated['payment_status'],
                'notes' => $validated['notes'] ?? null,
            ])
            ->log('status updated');

        return redirect()->route('admin.orders.show', $order)
                       ->with('success', 'Order status updated successfully!');
    }

    /**
     * Get order statistics
     */
    public function getStats()
    {
        $stats = [
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'confirmed_orders' => Order::where('status', 'confirmed')->count(),
            'processing_orders' => Order::where('status', 'processing')->count(),
            'shipped_orders' => Order::where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('status', 'cancelled')->count(),
            'paid_orders' => Order::where('payment_status', 'paid')->count(),
            'pending_payment_orders' => Order::where('payment_status', 'pending')->count(),
            'total_revenue' => Order::where('payment_status', 'paid')->sum('total'),
            'average_order_value' => Order::where('payment_status', 'paid')->avg('total'),
        ];

        return response()->json($stats);
    }

    /**
     * Bulk update order status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,refunded',
        ]);

        $orders = Order::whereIn('id', $validated['order_ids'])->get();

        foreach ($orders as $order) {
            $oldStatus = $order->status;
            $order->update(['status' => $validated['status']]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($order)
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status'],
                    'bulk_update' => true,
                ])
                ->log('status updated');
        }

        return redirect()->back()
                       ->with('success', count($orders) . ' orders updated successfully!');
    }

    /**
     * Export orders to CSV
     */
    public function export(Request $request)
    {
        $query = Order::query()->with('user');

        // Apply filters
        if ($request->has('status') && $request->get('status')) {
            $query->where('status', $request->get('status'));
        }

        if ($request->has('payment_status') && $request->get('payment_status')) {
            $query->where('payment_status', $request->get('payment_status'));
        }

        if ($request->has('from_date') && $request->get('from_date')) {
            $query->whereDate('created_at', '>=', $request->get('from_date'));
        }

        if ($request->has('to_date') && $request->get('to_date')) {
            $query->whereDate('created_at', '<=', $request->get('to_date'));
        }

        $orders = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="orders_' . now()->format('Y-m-d_His') . '.csv"',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Total',
                'Status',
                'Payment Status',
                'Payment Method',
                'Created At',
            ]);

            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->customer_name,
                    $order->customer_email,
                    $order->total,
                    ucfirst(str_replace('_', ' ', $order->status)),
                    ucfirst(str_replace('_', ' ', $order->payment_status)),
                    $order->payment_method,
                    $order->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Print order invoice
     */
    public function printInvoice(Order $order)
    {
        $order->load(['user', 'items']);
        return view('admin.orders.invoice', compact('order'));
    }
}
