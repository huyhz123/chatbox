<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentGateways\VNPayGateway;
use App\Services\PaymentGateways\MomoGateway;
use App\Services\PaymentGateways\ZaloPayGateway;
use App\Services\PaymentGateways\StripeGateway;
use App\Services\PaymentGateways\PayPalGateway;
use App\Services\PaymentGateways\USDTGateway;
use App\Services\PaymentGateways\FakeGateway;
use Exception;

class PaymentService
{
    protected $gateways = [
        'vnpay' => VNPayGateway::class,
        'momo' => MomoGateway::class,
        'zalopay' => ZaloPayGateway::class,
        'stripe' => StripeGateway::class,
        'paypal' => PayPalGateway::class,
        'usdt' => USDTGateway::class,
        'fake' => FakeGateway::class,
    ];

    public function createPayment(Order $order, string $gateway, array $data = [])
    {
        if (!isset($this->gateways[$gateway])) {
            throw new Exception("Payment gateway not supported: {$gateway}");
        }

        $gatewayClass = new $this->gateways[$gateway]();

        $payment = Payment::create([
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'payment_gateway' => $gateway,
            'amount' => $order->total,
            'currency' => $order->currency,
            'status' => 'pending',
            'request_data' => $data,
        ]);

        try {
            $result = $gatewayClass->createPayment($order, $payment, $data);
            $payment->update(['request_data' => $result]);
            return $result;
        } catch (Exception $e) {
            $payment->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    public function handleCallback(string $gateway, array $data)
    {
        if (!isset($this->gateways[$gateway])) {
            throw new Exception("Payment gateway not supported: {$gateway}");
        }

        $gatewayClass = new $this->gateways[$gateway]();
        return $gatewayClass->handleCallback($data);
    }

    public function verifyPayment(Payment $payment)
    {
        $gateway = $payment->payment_gateway;

        if (!isset($this->gateways[$gateway])) {
            return false;
        }

        $gatewayClass = new $this->gateways[$gateway]();
        return $gatewayClass->verifyPayment($payment);
    }

    public function processSuccessfulPayment(Payment $payment)
    {
        $order = $payment->order;

        $payment->markAsCompleted();
        $order->markAsPaid($payment->transaction_id);

        // Process order items based on type
        foreach ($order->items as $item) {
            $this->processOrderItem($order, $item);
        }

        // Send notification
        app(NotificationService::class)->sendPaymentConfirmation($order);

        return true;
    }

    protected function processOrderItem($order, $item)
    {
        $itemable = $item->itemable;

        if ($item->itemable_type === 'App\\Models\\Service') {
            // Create ticket for service
            app(TicketService::class)->createTicket($order, $itemable, $item->options);
        } elseif ($item->itemable_type === 'App\\Models\\File') {
            // Unlock file download
            app(FileService::class)->unlockFile($order->user, $itemable, $order);
        } elseif ($item->itemable_type === 'App\\Models\\Course') {
            // Enroll user in course
            app(CourseService::class)->enrollUser($order->user, $itemable, $order);
        } elseif ($item->itemable_type === 'App\\Models\\Product') {
            // Decrease product stock
            $itemable->decreaseStock($item->quantity);
            $itemable->incrementSoldCount($item->quantity);
        }
    }

    public function convertCurrency($amount, $fromCurrency, $toCurrency)
    {
        $currencies = config('payment.currencies');

        if (!isset($currencies[$fromCurrency]) || !isset($currencies[$toCurrency])) {
            return $amount;
        }

        // Convert to base currency (VND) first
        $baseAmount = $amount / $currencies[$fromCurrency]['rate'];

        // Convert to target currency
        return $baseAmount * $currencies[$toCurrency]['rate'];
    }
}
