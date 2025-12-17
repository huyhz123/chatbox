<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $pdfService;

    public function __construct(PdfService $pdfService)
    {
        $this->pdfService = $pdfService;
    }

    /**
     * Display list of user's orders
     */
    public function index()
    {
        $orders = Order::where('user_id', auth()->id())
            ->with(['items.itemable', 'payment'])
            ->latest()
            ->paginate(15);

        return view('frontend.orders.index', compact('orders'));
    }

    /**
     * Display order details
     */
    public function show(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        $order->load(['items.itemable', 'payment', 'shippingMethod']);

        return view('frontend.orders.show', compact('order'));
    }

    /**
     * Cancel an order
     */
    public function cancel(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized access to this order.');
        }

        // Check if order can be cancelled
        if (!in_array($order->status, ['pending', 'processing'])) {
            return back()->with('error', 'This order cannot be cancelled. Status: ' . $order->status);
        }

        try {
            $order->status = 'cancelled';
            $order->save();

            // Log activity
            Log::info('Order cancelled by user', [
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'order_number' => $order->order_number,
            ]);

            // TODO: Trigger refund if payment was completed
            if ($order->payment && $order->payment->status === 'completed') {
                // Refund logic here
                Log::info('Refund should be initiated for order: ' . $order->order_number);
            }

            return back()->with('success', 'Order cancelled successfully. You will receive a refund if payment was completed.');
        } catch (\Exception $e) {
            Log::error('Error cancelling order: ' . $e->getMessage());
            return back()->with('error', 'Error cancelling order. Please contact support.');
        }
    }

    /**
     * Download order invoice as PDF
     */
    public function invoice(Order $order)
    {
        // Ensure user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to this order.');
        }

        try {
            $order->load(['items.itemable', 'user', 'shippingMethod']);

            // Generate PDF invoice
            $pdf = $this->pdfService->generateInvoice($order);

            if (!$pdf) {
                return back()->with('error', 'PDF generation not available. Please install barryvdh/laravel-dompdf package.');
            }

            return $pdf;
        } catch (\Exception $e) {
            Log::error('Error generating invoice: ' . $e->getMessage());
            return back()->with('error', 'Error generating invoice.');
        }
    }
}
