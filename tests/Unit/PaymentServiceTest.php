<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = new PaymentService();
    }

    /** @test */
    public function create_payment_for_supported_gateway()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100000,
            'currency' => 'VND',
        ]);

        $result = $this->paymentService->createPayment($order, 'fake');

        $this->assertIsArray($result);
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_gateway' => 'fake',
        ]);
    }

    /** @test */
    public function create_payment_throws_exception_for_unsupported_gateway()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment gateway not supported');

        $this->paymentService->createPayment($order, 'unsupported');
    }

    /** @test */
    public function create_payment_sets_pending_status()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 100000,
        ]);

        $this->paymentService->createPayment($order, 'fake');

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('pending', $payment->status);
    }

    /** @test */
    public function create_payment_stores_order_and_user_id()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->paymentService->createPayment($order, 'fake');

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals($order->id, $payment->order_id);
        $this->assertEquals($user->id, $payment->user_id);
    }

    /** @test */
    public function handle_callback_calls_gateway_handler()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'total' => 100000]);

        $result = $this->paymentService->handleCallback('fake', [
            'amount' => 100000,
        ]);

        $this->assertIsArray($result);
    }

    /** @test */
    public function handle_callback_throws_exception_for_unsupported_gateway()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Payment gateway not supported');

        $this->paymentService->handleCallback('unsupported', []);
    }

    /** @test */
    public function verify_payment_returns_false_for_unsupported_gateway()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'payment_gateway' => 'unsupported_gateway',
        ]);

        $result = $this->paymentService->verifyPayment($payment);

        $this->assertFalse($result);
    }

    /** @test */
    public function process_successful_payment_marks_payment_completed()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $this->paymentService->processSuccessfulPayment($payment);

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'completed',
        ]);
    }

    /** @test */
    public function process_successful_payment_marks_order_as_paid()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);
        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'transaction_id' => 'TRX-123',
            'status' => 'pending',
        ]);

        $this->paymentService->processSuccessfulPayment($payment);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
        ]);
    }

    /** @test */
    public function process_successful_payment_with_product_order_item()
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['stock' => 100]);
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'payment_status' => 'pending',
        ]);

        OrderItem::factory()->create([
            'order_id' => $order->id,
            'itemable_type' => 'App\\Models\\Product',
            'itemable_id' => $product->id,
            'quantity' => 2,
        ]);

        $payment = Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'pending',
        ]);

        $this->paymentService->processSuccessfulPayment($payment);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 98,
        ]);
    }

    /** @test */
    public function convert_currency_same_currency_returns_same_amount()
    {
        $amount = 1000000;

        $result = $this->paymentService->convertCurrency($amount, 'VND', 'VND');

        $this->assertEquals($amount, $result);
    }

    /** @test */
    public function supported_gateways_include_all_required()
    {
        $gateways = ['vnpay', 'momo', 'zalopay', 'stripe', 'paypal', 'usdt', 'fake'];

        foreach ($gateways as $gateway) {
            $user = User::factory()->create();
            $order = Order::factory()->create(['user_id' => $user->id, 'total' => 100000]);

            $result = $this->paymentService->createPayment($order, $gateway);

            $this->assertIsArray($result);
        }
    }

    /** @test */
    public function create_payment_stores_request_data()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $data = ['custom' => 'value'];

        $this->paymentService->createPayment($order, 'fake', $data);

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertIsArray($payment->request_data);
    }

    /** @test */
    public function payment_service_creates_payment_with_correct_amount()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'total' => 500000,
        ]);

        $this->paymentService->createPayment($order, 'fake');

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals(500000, $payment->amount);
    }

    /** @test */
    public function payment_service_creates_payment_with_correct_currency()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create([
            'user_id' => $user->id,
            'currency' => 'USD',
        ]);

        $this->paymentService->createPayment($order, 'fake');

        $payment = Payment::where('order_id', $order->id)->first();
        $this->assertEquals('USD', $payment->currency);
    }

    /** @test */
    public function process_successful_payment_returns_true()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);
        $payment = Payment::factory()->create(['order_id' => $order->id]);

        $result = $this->paymentService->processSuccessfulPayment($payment);

        $this->assertTrue($result);
    }

    /** @test */
    public function multiple_payments_for_same_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $this->paymentService->createPayment($order, 'fake');
        $this->paymentService->createPayment($order, 'fake');

        $payments = Payment::where('order_id', $order->id)->get();

        $this->assertEquals(2, $payments->count());
    }
}
