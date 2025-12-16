<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Display payment gateway selection page
     */
    public function select(Order $order): View
    {
        // Verify user owns this order
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }

        // Verify order is pending
        if ($order->status !== 'pending' || $order->payment_status !== 'pending') {
            abort(422, 'This order cannot be paid');
        }

        $paymentMethods = [
            'vnpay' => [
                'name' => 'VNPay',
                'description' => 'Pay with VNPay',
                'icon' => 'vnpay',
            ],
            'momo' => [
                'name' => 'Momo',
                'description' => 'Pay with Momo',
                'icon' => 'momo',
            ],
            'zalopay' => [
                'name' => 'ZaloPay',
                'description' => 'Pay with ZaloPay',
                'icon' => 'zalopay',
            ],
            'stripe' => [
                'name' => 'Stripe',
                'description' => 'Pay with Stripe (Credit Card)',
                'icon' => 'stripe',
            ],
            'paypal' => [
                'name' => 'PayPal',
                'description' => 'Pay with PayPal',
                'icon' => 'paypal',
            ],
            'usdt' => [
                'name' => 'USDT (Crypto)',
                'description' => 'Pay with USDT',
                'icon' => 'usdt',
            ],
            'fake' => [
                'name' => 'Demo Payment',
                'description' => 'Test payment gateway (Demo only)',
                'icon' => 'fake',
            ],
        ];

        // Filter available payment methods
        $availablePaymentMethods = array_filter($paymentMethods, function ($key) {
            return config('payment.gateways.' . $key . '.enabled', false);
        }, ARRAY_FILTER_USE_KEY);

        return view('frontend.payment.select', [
            'order' => $order,
            'paymentMethods' => $availablePaymentMethods,
        ]);
    }

    /**
     * Initiate payment
     */
    public function initiate(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_gateway' => 'required|string',
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
                'message' => 'This order cannot be paid',
            ], 422);
        }

        try {
            $paymentData = $this->paymentService->createPayment(
                $order,
                $request->payment_gateway,
                [
                    'return_url' => route('payment.callback', [
                        'gateway' => $request->payment_gateway,
                        'order_id' => $order->id,
                    ]),
                ]
            );

            // For redirect-based gateways
            if (isset($paymentData['redirect_url'])) {
                return response()->json([
                    'success' => true,
                    'message' => 'Redirecting to payment gateway',
                    'redirect' => $paymentData['redirect_url'],
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated',
                'data' => $paymentData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to initiate payment: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle payment callback
     */
    public function callback(Request $request, $gateway): Response
    {
        try {
            $result = $this->paymentService->handleCallback($gateway, $request->all());

            if ($result['success']) {
                return redirect()->route('payment.success', [
                    'order_id' => $result['order_id'] ?? null,
                    'message' => 'Payment successful',
                ]);
            }

            return redirect()->route('payment.failed', [
                'order_id' => $result['order_id'] ?? null,
                'message' => $result['message'] ?? 'Payment failed',
            ]);
        } catch (\Exception $e) {
            return redirect()->route('payment.failed', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * VNPay callback
     */
    public function vnpayCallback(Request $request): Response
    {
        return $this->callback($request, 'vnpay');
    }

    /**
     * Momo callback
     */
    public function momoCallback(Request $request): Response
    {
        return $this->callback($request, 'momo');
    }

    /**
     * ZaloPay callback
     */
    public function zaloPayCallback(Request $request): Response
    {
        return $this->callback($request, 'zalopay');
    }

    /**
     * Stripe callback
     */
    public function stripeCallback(Request $request): Response
    {
        return $this->callback($request, 'stripe');
    }

    /**
     * PayPal callback
     */
    public function paypalCallback(Request $request): Response
    {
        return $this->callback($request, 'paypal');
    }

    /**
     * USDT callback
     */
    public function usdtCallback(Request $request): Response
    {
        return $this->callback($request, 'usdt');
    }

    /**
     * Fake gateway callback
     */
    public function fakeCallback(Request $request): Response
    {
        return $this->callback($request, 'fake');
    }

    /**
     * Payment success page
     */
    public function success(Request $request): View
    {
        $orderId = $request->input('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && $order->user_id !== auth()->id()) {
                abort(403, 'Unauthorized');
            }
        }

        return view('frontend.payment.success', [
            'order' => $order,
            'message' => $request->input('message', 'Payment processed successfully'),
        ]);
    }

    /**
     * Payment failed page
     */
    public function failed(Request $request): View
    {
        $orderId = $request->input('order_id');
        $order = null;

        if ($orderId) {
            $order = Order::find($orderId);
            if ($order && $order->user_id !== auth()->id()) {
                abort(403, 'Unauthorized');
            }
        }

        return view('frontend.payment.failed', [
            'order' => $order,
            'message' => $request->input('message', 'Payment failed'),
        ]);
    }

    /**
     * Get payment status
     */
    public function status(Order $order): JsonResponse
    {
        // Verify user owns this order
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $payment = $order->payments()->latest()->first();

        if (!$payment) {
            return response()->json([
                'success' => false,
                'message' => 'No payment found',
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'status' => $payment->status,
            'order_status' => $order->payment_status,
            'payment' => $payment,
        ]);
    }

    /**
     * Verify payment
     */
    public function verify(Order $order): JsonResponse
    {
        // Verify user owns this order
        if ($order->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $payment = $order->payments()->latest()->first();

            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payment found',
                ], 422);
            }

            $isVerified = $this->paymentService->verifyPayment($payment);

            if ($isVerified) {
                $this->paymentService->processSuccessfulPayment($payment);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment verified',
                    'redirect' => route('payment.success', $order->id),
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Verification error: ' . $e->getMessage(),
            ], 422);
        }
    }
}
